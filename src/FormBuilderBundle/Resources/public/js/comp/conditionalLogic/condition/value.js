pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition.value');
Formbuilder.comp.conditionalLogic.condition.value = Class.create(Formbuilder.comp.conditionalLogic.condition.abstract, {

    name: 'field value',

    getItem: function () {
        var _ = this;
        var conditionTypesStore = Ext.create('Ext.data.Store', {
            fields: ['label', 'value'],
            data: [{
                label: 'Is selected',
                value: 'is_selected'
            }, {
                label: 'Is greater',
                value: 'is_greater'
            }, {
                label: 'Is Less',
                value: 'is_less'
            }, {
                label: 'Is value',
                value: 'is_value'
            }]
        });

        var fieldStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'display_name'],
            data: this.panel.getFormFields().fields
        });

        var items = [{
            xtype: 'hidden',
            name: _.generateFieldName(this.sectionId, this.index, 'type'),
            value: 'value',
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'type');
                }
            }
        },
        {
            xtype: 'tagfield',
            name: _.generateFieldName(this.sectionId, this.index, 'fields'),
            fieldLabel: t('form_builder_condition_fields'),
            queryDelay: 0,
            stacked: true,
            displayField: 'display_name',
            valueField: 'name',
            mode: 'local',
            labelAlign: 'top',
            store: fieldStore,
            editable: false,
            triggerAction: 'all',
            anchor: '100%',
            value: this.data ? this.data.fields : null,
            allowBlank: false,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'fields');
                }
            }
        }, {
            xtype: 'combo',
            name: _.generateFieldName(this.sectionId, this.index, 'comparator'),
            fieldLabel: t('form_builder_condition_type'),
            queryDelay: 0,
            displayField: 'label',
            valueField: 'value',
            mode: 'local',
            labelAlign: 'top',
            store: conditionTypesStore,
            editable: true,
            triggerAction: 'all',
            anchor: '100%',
            value: this.data ? this.data.comparator : null,
            summaryDisplay: true,
            allowBlank: false,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'comparator');
                }
            }
        },
        {
            xtype: 'textfield',
            name: _.generateFieldName(this.sectionId, this.index, 'value'),
            fieldLabel: t('form_builder_condition_value'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: true,
            value: this.data ? this.data.value : null,
            flex: 1,
            listeners: {
                updateIndexName: function(sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value');
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
                tbar: this.getTopBar(_.name, myId, 'form_builder_icon_text'),
                items: compositeField,
                listeners: {}
            });

        return item;
    }
});
