pimcore.registerNS('Formbuilder.extjs.conditionalLogic.condition');
pimcore.registerNS('Formbuilder.extjs.conditionalLogic.condition.outputWorkflow');
Formbuilder.extjs.conditionalLogic.condition.outputWorkflow = Class.create(Formbuilder.extjs.conditionalLogic.condition.abstract, {

    getItem: function () {
        var _ = this,

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
                    xtype: 'tagfield',
                    name: _.generateFieldName(this.sectionId, this.index, 'outputWorkflows'),
                    fieldLabel: t('form_builder_output_workflow_select'),
                    style: 'margin: 0 5px 0 0',
                    labelAlign: 'top',
                    anchor: '100%',
                    stacked: true,
                    displayField: 'name',
                    valueField: 'id',
                    allowBlank: false,
                    flex: 1,
                    queryMode: 'local',
                    selectOnFocus: false,
                    editable: false,
                    triggerAction: 'all',
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
                    value: null,
                    listeners: {
                        afterrender: function (cb) {
                            cb.store.load({
                                callback: function () {
                                    var value = this.data ? this.checkFieldAvailability(this.data.outputWorkflows, cb.store, 'id') : null;
                                    cb.setValue(value);
                                }.bind(this)
                            });
                        }.bind(this),
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'outputWorkflows');
                        }
                    }
                }
            ],
            compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: 'padding-bottom:5px;',
                items: items
            }),
            fieldId = Ext.id();

        return new Ext.form.FormPanel({
            id: fieldId,
            type: 'combo',
            forceLayout: true,
            style: 'margin: 10px 0 0 0',
            bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
            tbar: this.getTopBar(fieldId),
            items: [compositeField],
            listeners: {}
        });
    }
});
