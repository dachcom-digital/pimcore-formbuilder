pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.changeConstraints');
Formbuilder.comp.conditionalLogic.action.changeConstraints = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

    name: 'change field validation',

    getItem: function () {

        var constraintTypesStore = Ext.create('Ext.data.Store', {
            fields: ['label', 'value'],
            data: []
        });

        var items = [{
            xtype: 'combo',
            name: 'conditions.action.' + this.sectionId + '.change_constraint.' + this.index + '.validation',
            fieldLabel: t('form_builder_constraint_type'),
            queryDelay: 0,
            displayField: 'label',
            valueField: 'value',
            mode: 'local',
            labelAlign: 'top',
            store: constraintTypesStore,
            editable: true,
            triggerAction: 'all',
            anchor: '100%',
            //value: todo,
            summaryDisplay: true,
            allowBlank: false,
            flex: 1,
            //listeners: null
        }
        ];

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
