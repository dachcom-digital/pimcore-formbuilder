pimcore.registerNS('Formbuilder.extjs.conditionalLogic.action');
pimcore.registerNS('Formbuilder.extjs.conditionalLogic.action.switchOutputWorkflow');
Formbuilder.extjs.conditionalLogic.action.switchOutputWorkflow = Class.create(Formbuilder.extjs.conditionalLogic.action.abstract, {

    valueField: null,
    fieldPanel: null,

    getItem: function () {

        var _ = this,
            fieldId = Ext.id(),
            items = [
                {
                    xtype: 'hidden',
                    name: _.generateFieldName(this.sectionId, this.index, 'type'),
                    value: this.fieldConfiguration.identifier,
                    listeners: {
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'type');
                        }
                    }
                },
                {
                    xtype: 'combo',
                    name: _.generateFieldName(this.sectionId, this.index, 'workflowName'),
                    fieldLabel: t('form_builder_switch_output_workflow_identifier'),
                    anchor: '100%',
                    queryDelay: 0,
                    displayField: 'name',
                    valueField: 'name',
                    mode: 'local',
                    labelAlign: 'top',
                    allowBlank: false,
                    editable: false,
                    triggerAction: 'all',
                    value: null,
                    store: new Ext.data.Store({
                        autoLoad: false,
                        proxy: {
                            type: 'ajax',
                            url: '/admin/formbuilder/output-workflow/get-output-workflow-list/' + this.panel.getFormId(),
                            fields: ['id', 'name'],
                            reader: {
                                type: 'json',
                                rootProperty: 'outputWorkflows'
                            },
                        }
                    }),
                    listeners: {
                        afterrender: function (cb) {
                            cb.store.load({
                                callback: function () {
                                    var value = this.data ? this.checkFieldAvailability([this.data.workflowName], cb.store, 'name') : null;
                                    cb.setValue(value !== null && value.length > 0 ? value[0] : null);
                                }.bind(this)
                            });
                        }.bind(this),
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'outputWorkflows');
                        }
                    }
                }
            ];

        this.fieldPanel = new Ext.form.FormPanel({
            id: fieldId,
            forceLayout: true,
            style: 'margin: 10px 0 0 0',
            bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
            tbar: this.getTopBar(fieldId),
            items: items
        });

        return this.fieldPanel;
    }
});
