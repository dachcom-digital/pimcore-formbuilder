pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.mailBehaviour');
Formbuilder.comp.conditionalLogic.action.mailBehaviour = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

    getItem: function () {
        var _ = this;
        var fieldStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'display_name'],
            data: this.panel.getFormFields().fields
        });

        var items = [{
            xtype: 'hidden',
            name:  _.generateFieldName(this.sectionId, this.index, 'type'),
            value: this.fieldConfiguration.identifier,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'type');
                }
            }
        },
        {
            xtype: 'combo',
            name: _.generateFieldName(this.sectionId, this.index, 'identifier'),
            fieldLabel: t('form_builder_mail_behaviour_identifier'),
            style: 'margin: 0 5px 0 0',
            queryDelay: 0,
            displayField: 'key',
            valueField: 'value',
            mode: 'local',
            labelAlign: 'top',
            store: new Ext.data.ArrayStore({
                fields: ['value', 'key'],
                data: [
                    ['recipient', t('form_builder_mail_behaviour_identifier_recipient')]
                ]
            }),
            editable: true,
            triggerAction: 'all',
            anchor: '100%',
            value: this.data ? this.data.identifier : null,
            summaryDisplay: true,
            allowBlank: false,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'identifier');
                }
            }
        },
        {
            xtype: 'textfield',
            name: _.generateFieldName(this.sectionId, this.index, 'value'),
            fieldLabel: t('form_builder_mail_behaviour_mail_value'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: false,
            value: this.data ? this.data.value : null,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value');
                }
            }
        }];

        var compositeField = new Ext.form.FieldContainer({
            layout: 'hbox',
            hideLabel: true,
            style: 'padding-bottom:5px;',
            items: items
        });


        var _ = this,
            myId = Ext.id(),
            item = new Ext.form.FormPanel({
                id: myId,
                type: 'combo',
                forceLayout: true,
                style: 'margin: 10px 0 0 0',
                bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
                tbar: this.getTopBar(myId),
                items: compositeField,
                listeners: {}
            });

        return item;
    }
});
