pimcore.registerNS('Formbuilder.extjs.rootForm');
Formbuilder.extjs.rootForm = Class.create({

    parentPanel: null,
    panel: null,
    formConfigurationPanel: null,
    formOutputWorkflowPanel: null,

    formData: null,
    formId: null,
    formName: null,
    sensitiveFormFields: null,

    /**
     * @param formData
     * @param parentPanel
     */
    initialize: function (formData, parentPanel) {

        this.parentPanel = parentPanel;
        this.formData = formData;
        this.formId = formData.id;
        this.formName = formData.name;
        this.sensitiveFormFields = {};
        this.sensitiveFormFieldsBackup = {};

        Formbuilder.eventObserver.registerForm(this.formId);

        if (formData.has_output_workflows === true) {
            this.sensitiveFormFields = formData.sensitive_field_names;
            this.sensitiveFormFieldsBackup = Ext.isObject(this.sensitiveFormFields) ? Ext.apply({}, this.sensitiveFormFields) : {};
        }

        this.addLayout();
    },

    remove: function () {
        this.panel.destroy();
    },

    addLayout: function () {

        var eventListeners = Formbuilder.eventObserver.getObserver(this.formId).on({
            'output_workflow.required_form_fields_refreshed': this.onSensitiveFormFieldsRefreshed.bind(this),
            'output_workflow.required_form_fields_updated': this.onSensitiveFormFieldsUpdated.bind(this),
            'output_workflow.required_form_fields_persisted': this.onSensitiveFormFieldsPersisted.bind(this),
            'output_workflow.required_form_fields_reset': this.onSensitiveFormFieldsReset.bind(this),
            destroyable: true
        });

        this.formConfiguration = new Formbuilder.extjs.formPanel.config(this.formData, this.parentPanel);
        this.formOutputWorkflow = new Formbuilder.extjs.formPanel.outputWorkflowPanel(this.formData, this.parentPanel);

        this.panel = new Ext.TabPanel({
            title: this.formName + ' (ID: ' + this.formId + ')',
            closable: true,
            cls: 'form-builder-form-panel',
            iconCls: 'form_builder_icon_root',
            autoScroll: true,
            autoEl: {
                'data-form-id': this.formId
            },
            border: false,
            layout: 'border'

        });

        this.formConfigurationPanel = this.formConfiguration.getLayout(this.panel);
        this.formConfigurationPanel.on('afterrender', this.checkSensitiveFields.bind(this));

        this.formOutputWorkflowPanel = this.formOutputWorkflow.getLayout(this.panel);
        this.formConfigurationPanel.on('beforeactivate', this.checkSensitiveFieldsInEditPanel.bind(this));

        this.panel.add([this.formConfigurationPanel, this.formOutputWorkflowPanel]);

        this.panel.on({
            beforedestroy: function () {
                eventListeners.destroy();
                Formbuilder.eventObserver.unregisterForm(this.formId);

                if (this.formId && this.parentPanel.panels['form_' + this.formId]) {
                    delete this.parentPanel.panels['form_' + this.formId];
                }

                if (this.parentPanel.tree.initialConfig !== null &&
                    Object.keys(this.parentPanel.panels).length === 0) {
                    this.parentPanel.tree.getSelectionModel().deselectAll();
                }
            }.bind(this),
            render: function () {
                this.setActiveTab(0);
            }
        });

        this.parentPanel.getEditPanel().add(this.panel);
        this.parentPanel.getEditPanel().setActiveTab(this.panel);

        pimcore.layout.refresh();
    },

    activate: function () {
        this.parentPanel.getEditPanel().setActiveTab(this.panel);
    },

    onSensitiveFormFieldsRefreshed: function (data) {

        if (data.hasOwnProperty('workflowId')) {
            this.sensitiveFormFields[data.workflowId] = [];
            this.checkSensitiveFields();
        }

        // broadcast to every active channel to provide require forms!
        Formbuilder.eventObserver.getObserver(this.formId).fireEvent('output_workflow.required_form_fields_requested');
    },

    onSensitiveFormFieldsReset: function (data) {

        if (!data.hasOwnProperty('workflowId')) {
            return;
        }

        if (!this.sensitiveFormFieldsBackup.hasOwnProperty(data.workflowId)) {
            this.sensitiveFormFieldsBackup[data.workflowId] = [];
        }

        if (!this.sensitiveFormFields.hasOwnProperty(data.workflowId)) {
            this.sensitiveFormFields[data.workflowId] = [];
        }

        this.sensitiveFormFields[data.workflowId] = Ext.Array.clone(this.sensitiveFormFieldsBackup[data.workflowId]);

        this.checkSensitiveFields();
    },

    onSensitiveFormFieldsUpdated: function (data) {

        if (!data.hasOwnProperty('fields') || !data.hasOwnProperty('workflowId') || !Ext.isArray(data.fields)) {
            return;
        }

        if (!this.sensitiveFormFields.hasOwnProperty(data.workflowId)) {
            this.sensitiveFormFields[data.workflowId] = [];
        }

        this.sensitiveFormFields[data.workflowId] = Ext.Array.merge(data.fields, this.sensitiveFormFields[data.workflowId]);

        this.checkSensitiveFields();
    },

    onSensitiveFormFieldsPersisted: function (data) {

        if (!data.hasOwnProperty('workflowId')) {
            return;
        }

        if (!this.sensitiveFormFieldsBackup.hasOwnProperty(data.workflowId)) {
            this.sensitiveFormFieldsBackup[data.workflowId] = [];
        }

        if (!this.sensitiveFormFields.hasOwnProperty(data.workflowId)) {
            this.sensitiveFormFields[data.workflowId] = [];
        }

        this.sensitiveFormFieldsBackup[data.workflowId] = Ext.Array.clone(this.sensitiveFormFields[data.workflowId]);

        this.checkSensitiveFields();
    },

    checkSensitiveFields: function () {

        this.formConfiguration.tree.getRootNode().cascade(function (record) {
            var nodeClass = record.get('cls');
            if (record.get('fbSensitiveFieldName') !== undefined) {
                record.set('cls', Ext.isString(nodeClass) ? nodeClass.replace('form_builder_output_workflow_aware_form_item', '') : '');
                record.set('fbSensitiveLocked', false);
            }
        });

        Ext.Object.each(this.sensitiveFormFields, function (workflowId, workflowFields) {
            if (Ext.isArray(workflowFields)) {
                Ext.Array.each(workflowFields, function (sensitiveFieldName) {
                    var record = this.formConfiguration.tree.getRootNode().findChild('fbSensitiveFieldName', sensitiveFieldName, true),
                        nodeClass;
                    if (record !== null) {
                        nodeClass = record.get('cls');
                        nodeClass = Ext.isString(nodeClass) ? nodeClass.replace('form_builder_output_workflow_aware_form_item', '') : '';
                        record.set('cls', (nodeClass + ' form_builder_output_workflow_aware_form_item').replace('  ', ' '));
                        record.set('fbSensitiveLocked', true);
                    }
                }.bind(this));
            }
        }.bind(this));
    },

    checkSensitiveFieldsInEditPanel: function () {

        var nameField, record;

        if (!this.formConfiguration.editPanel) {
            return;
        }

        nameField = this.formConfiguration.editPanel.query('tabpanel textfield[name="name"]');
        if (nameField.length !== 1) {
            return;
        }

        record = this.formConfiguration.tree.getRootNode().findChild('fbSensitiveFieldName', nameField[0].getValue(), true);
        if (!record) {
            return;
        }

        if (record.get('fbSensitiveLocked') === undefined) {
            return;
        }

        nameField[0].setReadOnly(record.get('fbSensitiveLocked'));
    }
});