pimcore.registerNS('Formbuilder.extjs.rootForm');
Formbuilder.extjs.rootForm = Class.create({

    parentPanel: null,
    formId: null,
    formName: null,
    formData: null,

    /**
     * @param formData
     * @param parentPanel
     */
    initialize: function (formData, parentPanel) {

        this.parentPanel = parentPanel;
        this.formId = formData.id;
        this.formName = formData.name;
        this.formData = formData;

        this.addLayout();
    },

    remove: function () {
        this.panel.destroy();
    },

    addLayout: function () {

        var formConfigurationPanel = new Formbuilder.extjs.formPanel.config(this.formData, this.parentPanel),
            formOutputWorkflowPanel = new Formbuilder.extjs.formPanel.outputWorkflowPanel(this.formData, this.parentPanel);

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

        this.panel.add([formConfigurationPanel.getLayout(this.panel), formOutputWorkflowPanel.getLayout(this.panel)]);

        this.panel.on('beforedestroy', function () {

            if (this.formId && this.parentPanel.panels['form_' + this.formId]) {
                delete this.parentPanel.panels['form_' + this.formId];
            }

            if (this.parentPanel.tree.initialConfig !== null &&
                Object.keys(this.parentPanel.panels).length === 0) {
                this.parentPanel.tree.getSelectionModel().deselectAll();
            }

        }.bind(this));

        this.panel.on('render', function () {
            this.setActiveTab(0);
        });

        this.parentPanel.getEditPanel().add(this.panel);
        this.parentPanel.getEditPanel().setActiveTab(this.panel);

        pimcore.layout.refresh();
    },

    activate: function () {
        this.parentPanel.getEditPanel().setActiveTab(this.panel);
    }
});