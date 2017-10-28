pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.toggle');
Formbuilder.comp.conditionalLogic.action.toggle = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

    name: 'toggle field',

    getItem: function () {

        var toggleTypesStore = Ext.create('Ext.data.Store', {
            fields: ['label', 'value'],
            data: [{
                label: 'Show',
                value: 'show'
            }, {
                label: 'Hide',
                value: 'hide'
            }]
        });

        var items = [{
            xtype: 'combo',
            name: 'condition_type',
            fieldLabel: t('form_builder_toggle_type'),
            queryDelay: 0,
            displayField: 'label',
            valueField: 'value',
            mode: 'local',
            labelAlign: 'top',
            store: toggleTypesStore,
            editable: true,
            triggerAction: 'all',
            anchor: '100%',
            //value: todo,
            summaryDisplay: true,
            allowBlank: false,
            flex: 1,
            //listeners: null
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
                tbar: this.getTopBar(_.name, myId, 'form_builder_icon_text'),
                items: compositeField,
                listeners: {}
            });

        return item;
    }
});
