pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.constraintsAdd');
Formbuilder.comp.conditionalLogic.action.constraintsAdd = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

    getItem: function () {
        var _ = this;

        var constraintTypesStore = Ext.create('Ext.data.Store', {
            fields: ['label', 'id'],
            data: this.panel.getFormConstraints()
        });

        var fieldStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'display_name'],
            data: this.panel.getFormFields().fields
        });

        var items = [{
            xtype: 'hidden',
            name: _.generateFieldName(this.sectionId, this.index, 'type'),
            value: this.fieldConfiguration.identifier,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'type');
                }
            }
        },
        {
            xtype: 'tagfield',
            name: _.generateFieldName(this.sectionId, this.index, 'fields'),
            fieldLabel: t('form_builder_constraints_fields'),
            style: 'margin: 0 5px 0 0',
            queryDelay: 0,
            stacked: true,
            displayField: 'display_name',
            valueField: 'name',
            mode: 'local',
            labelAlign: 'top',
            store: fieldStore,
            editable: false,
            filterPickList: true,
            anchor: '100%',
            value: this.data ? this.data.fields : null,
            allowBlank: false,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'fields');
                }
            }
        },
        {
            xtype: 'tagfield',
            name: _.generateFieldName(this.sectionId, this.index, 'validation'),
            fieldLabel: t('form_builder_constraints_type'),
            queryDelay: 0,
            stacked: true,
            displayField: 'label',
            valueField: 'id',
            mode: 'local',
            labelAlign: 'top',
            store: constraintTypesStore,
            editable: false,
            filterPickList: true,
            anchor: '100%',
            value: this.data ? this.data.validation : null,
            allowBlank: false,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'validation');
                }
            }
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
                tbar: this.getTopBar(myId),
                items: compositeField,
                listeners: {}
            });

        return item;
    }
});
