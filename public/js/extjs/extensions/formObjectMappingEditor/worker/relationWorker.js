pimcore.registerNS('Formbuilder.extjs.extensions.formObjectMappingEditorWorker.relationWorker');
Formbuilder.extjs.extensions.formObjectMappingEditorWorker.relationWorker = Class.create({

    formId: null,
    classId: null,
    relationKey: null,
    data: null,

    window: null,
    editPanel: null,
    comboBox: null,
    node: null,

    hasValidData: false,

    initialize: function (formId, classId, relationKey, data) {

        this.formId = formId;
        this.classId = classId;
        this.relationKey = relationKey;
        this.data = data;

        this.hasValidData = false;

        if (data !== null) {
            if (data.hasOwnProperty('relationType') && data.relationType !== null) {
                this.hasValidData = true;
            }
        }
    },

    getName: function () {
        return 'relationWorker';
    },

    getConfigDialog: function (node, cb) {

        this.node = node;

        var comboBox = new Ext.form.ComboBox({
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_relation'),
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                queryMode: 'local',
                labelAlign: 'left',
                value: this.data !== null && this.data.hasOwnProperty('relationType') ? this.data.relationType : null,
                triggerAction: 'all',
                anchor: '100%',
                labelWidth: 180,
                editable: false,
                summaryDisplay: true,
                allowBlank: false,
                name: 'relationType'
            }),
            store = new Ext.data.ArrayStore({
                fields: ['value', 'label'],
                data: [
                    ['asset', t('asset')],
                    ['document', t('document')],
                    ['object', t('object')]
                ]
            });

        comboBox.setStore(store);

        this.comboBox = comboBox;

        this.window = new Ext.Window({
            width: 400,
            height: 200,
            iconCls: 'form_builder_output_workflow_channel_object_mapper',
            layout: 'fit',
            closeAction: 'destroy',
            plain: true,
            autoScroll: true,
            autoHeight: true,
            preventRefocus: true,
            cls: 'formbuilder-object-mapping-editor-relation',
            title: 'Relation Configurator',
            modal: true,
            items: [this.configPanel],
            buttons: [
                {
                    text: t('form_builder.output_workflow.apply_and_close'),
                    iconCls: 'form_builder_output_workflow_apply_data',
                    handler: this.commitData.bind(this, cb)
                }
            ]
        });

        this.window.addDocked({
            xtype: 'toolbar',
            dock: 'top',
            items: [this.comboBox]
        });

        this.window.show();

        return this.window;
    },

    isReadyToConfigure: function (node) {
        this.node = node;

        if (!this.node.parentNode) {
            return false;
        }

        return this.node.parentNode.data.omFieldTypeIdentifier === 'form_field';
    },

    isValid: function (node) {
        return this.hasValidData === true;
    },

    getData: function () {
        return this.data;
    },

    commitData: function (cb) {

        var relationType = this.comboBox.getValue();

        this.hasValidData = relationType !== null;

        this.data = {
            'relationType': relationType,
        };

        this.window.close();

        if (typeof cb === 'function') {
            cb();
        }
    }
});