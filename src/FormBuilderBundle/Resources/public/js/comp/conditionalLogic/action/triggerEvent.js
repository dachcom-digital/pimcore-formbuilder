pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.triggerEvent');
Formbuilder.comp.conditionalLogic.action.triggerEvent = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

    getItem: function () {
        var _ = this,
            fieldStore = Ext.create('Ext.data.Store', {
                fields: ['name', 'display_name'],
                data: this.panel.getFormFields().fields
            }),
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
                    name: _.generateFieldName(this.sectionId, this.index, 'fields'),
                    fieldLabel: t('form_builder_trigger_event_fields'),
                    style: 'margin: 0 5px 0 0',
                    queryDelay: 0,
                    stacked: true,
                    displayField: 'display_name',
                    valueField: 'name',
                    mode: 'local',
                    labelAlign: 'top',
                    store: fieldStore,
                    editable: false,
                    selectOnFocus: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    value: this.data ? this.checkFieldAvailability(this.data.fields, fieldStore, 'name') : null,
                    allowBlank: false,
                    flex: 1,
                    listeners: {
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'fields');
                        }
                    }
                },
                {
                    xtype: 'textfield',
                    name: _.generateFieldName(this.sectionId, this.index, 'event'),
                    fieldLabel: t('form_builder_trigger_event_event'),
                    anchor: '100%',
                    labelAlign: 'top',
                    summaryDisplay: true,
                    allowBlank: false,
                    maskRe: /[a-zA-Z0-9.]+/,
                    value: this.data ? this.data.event : null,
                    flex: 1,
                    listeners: {
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'event');
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
            descriptionField = new Ext.form.Label({
                xtype: 'label',
                anchor: '100%',
                style: 'display:block; padding:5px; background:#f5f5f5; border:1px solid #eee; font-weight: 300;',
                html: t('form_builder_trigger_event_description')
            }),
            fieldId = Ext.id();

        return new Ext.form.FormPanel({
            id: fieldId,
            type: 'combo',
            forceLayout: true,
            style: 'margin: 10px 0 0 0',
            bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
            tbar: this.getTopBar(fieldId),
            items: [compositeField, descriptionField],
            listeners: {}
        });
    }
});
