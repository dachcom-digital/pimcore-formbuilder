pimcore.registerNS('Formbuilder.comp.types.keyValueRepeater');
Formbuilder.comp.types.keyValueRepeater = Class.create({

    fieldConfig: null,

    storeData: null,

    repeater: null,

    type: 'default', //default |grouped

    optionType: 'user', //user | store

    optionStore: null,

    initialize: function(fieldConfig, storeData, optionStore) {

        this.fieldConfig = fieldConfig;
        this.storeData = storeData;
        this.optionStore = optionStore;

        if(this.optionStore) {
            this.optionType = 'store';
        }

        this.generateRepeaterWithKeyValue();

    },

    getRepeater: function() {
        return this.repeater;
    },

    generateRepeaterWithKeyValue: function() {

        var keyValueRepeater = null,
            metaDataCounter = 0,
            allowFirstOptionsEmpty = false;

        this.typeSelector = new Ext.form.ComboBox({
            width: 300,
            triggerAction:'all',
            store: [
                ['default', t('form_builder_repeater_default')],
                ['grouped', t('form_builder_repeater_grouped')]
            ],
            listeners: {
                select: function(combo, rec) {
                    this.type = combo.getValue();
                    combo.up().up().query('[name^=button_type_]').forEach(function(el) { el.hide() });
                    combo.up().up().query('[name="button_type_' + combo.getValue() + '"]')[0].show();
                }.bind(this)
            }
        });

        var items = [
            this.typeSelector,
            '->',
            {
                xtype: 'button',
                text: t('form_builder_add_field'),
                hidden: true,
                name: 'button_type_default',
                iconCls: 'pimcore_icon_add',
                handler: this.addMetaField.bind(this),
                tooltip: {
                    title:'',
                    text: t('form_builder_add_metadata')
                }
            },
            {
                xtype: 'button',
                text: t('form_builder_add_group'),
                hidden: true,
                name: 'button_type_grouped',
                iconCls: 'pimcore_icon_add',
                handler: this.addGroupedMetaField.bind(this),
                tooltip: {
                    title:'',
                    text: t('form_builder_add_grouped_metadata')
                }
            }
        ];

        if(allowFirstOptionsEmpty) {

            items.unshift( {
                xtype: 'panel',
                name: 'multiOptionsInfo',
                fieldLabel: '',
                submitValue : false,
                frame: false,
                border: false,
                bodyStyle: 'background:transparent;',
                flex: 1,
                html: t('form_builder_empty_multi_option_first_value')
            });
        }

        this.repeater = new Ext.form.FieldSet({

            title: this.fieldConfig.label,
            collapsible: false,
            autoHeight:true,
            width: '100%',
            style: 'margin-top: 20px;',
            name: 'meta',
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px;',
                items: items
            }]
        });

        this.populateRepeater();

        return this.repeater;

    },

    addGroupedMetaField: function(button, value) {

        var fieldSet = button.up().up();

        var groupFields = this.repeater.query('[name="meta_group"]'),
            fieldSetName = fieldSet.name,
            currentIndex = 0;

        if(groupFields.length > 0) {
            currentIndex = groupFields[groupFields.length -1].groupIndex + 1;
        }

        var items = [
            {
                xtype: 'textfield',
                text: t('form_builder_repeater_group_name'),
                name: this.fieldConfig.id + '.' + currentIndex + '.0.name',
                label: t('group_name'),
                value: typeof value !== 'object' ? value : null
            },
            '->',
            {
                xtype: 'button',
                text: t('form_builder_add_field'),
                name: 'add_field_button',
                iconCls: 'pimcore_icon_add',
                handler: this.addMetaField.bind(this)
            },
            {
                xtype: 'button',
                iconCls: 'pimcore_icon_delete',
                name: 'delete_group_button',
                style: 'float:left;',
                handler: function (compositeField, el) {
                    el.up().up().destroy();
                    this.checkTypeSelector();
                }.bind(this, compositeField)
            }
        ];

        var compositeField = new Ext.form.FieldSet({

            collapsible: false,
            autoHeight:true,
            width: '100%',
            style: 'margin-top: 20px;',
            name: 'meta_group',
            groupIndex: currentIndex,
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px;',
                items: items
            }]
        });

        fieldSet.add(compositeField);
        fieldSet.updateLayout();

        this.checkTypeSelector();

        return compositeField;

    },

    addMetaField: function(button, option, value) {

        var count = 0,
            namePrefix = '',
            fieldSet = button.up().up(),
            fieldSetName = fieldSet.name;

        count = fieldSet.query('[name="delete_button"]').length;

        if(this.type === 'grouped') {
            namePrefix = this.fieldConfig.id + '.' + fieldSet.groupIndex + '.' + count;
        } else {
            namePrefix = this.fieldConfig.id + '.' + count;
        }

        var optionField = null;

        if(this.optionType === 'user') {

            optionField = new Ext.form.TextField({
                name: namePrefix + '.option',
                fieldLabel: t('form_builder_option'),
                anchor: '100%',
                summaryDisplay: true,
                //allowBlank: allowFirstOptionsEmpty === true && metaDataCounter === 0,
                value : typeof option !== 'object' ? option : null,
                flex: 1,
                margin: '0 10px 0 0'
            });

        } else {

            var optionsStore = new Ext.data.ArrayStore({
                fields: ['label','value'],
                data : this.optionStore
            });

            optionField = new Ext.form.ComboBox({
                name: namePrefix + '.option',
                fieldLabel: t('form_builder_option'),
                queryDelay: 0,
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                store: optionsStore,
                editable: true,
                triggerAction: 'all',
                anchor: "100%",
                summaryDisplay: true,
                allowBlank: false,
                value : typeof option !== 'object' ? option : null,
            });

        }

        var compositeField = new Ext.form.FieldContainer({
            layout: 'hbox',
            hideLabel: true,
            style: 'padding-bottom:5px;',
            items: [
                optionField,
                {
                    xtype: 'textfield',
                    name: namePrefix + '.value',
                    fieldLabel: t('form_builder_value'),
                    anchor: '100%',
                    summaryDisplay: true,
                    allowBlank: false,
                    value : typeof value !== 'object' ? value : null,
                    flex: 1,
                    margin: '0 10px 0 0'
                }
            ]
        });

        compositeField.add([{
            xtype: 'button',
            iconCls: 'pimcore_icon_delete',
            style: 'float:left;',
            name: 'delete_button',
            handler: function (compositeField, el) {
                fieldSet.remove(compositeField);
                fieldSet.updateLayout();
                this.checkTypeSelector();
            }.bind(this, compositeField)
        },{
            xtype: 'box',
            style: 'clear:both;'
        }]);

        fieldSet.add(compositeField);
        fieldSet.updateLayout();

        this.checkTypeSelector();

    },

    populateRepeater: function() {

        var _ = this,
            type = null;

        if(!this.storeData || this.storeData.length === 0) {
            return;
        }

        //set selector first.
        Ext.Object.each(this.storeData, function(index, value) {

            if (Ext.isArray(value)) { //meta group
                type = 'grouped';
            } else { //meta default
                type = 'default';
            }

            _.typeSelector.setValue(type);
            _.typeSelector.fireEvent('select', _.typeSelector);
            return false;

        });

        Ext.Object.each(this.storeData, function(index, value) {
            if (type === 'grouped') {
                groupMetaField = undefined;
                Ext.Array.each(value, function(group) {
                    if(group.name) {
                        groupMetaField = _.addGroupedMetaField(_.repeater.query('[name="button_type_grouped"]')[0], group.name);
                    }
                    if(groupMetaField) {
                        _.addMetaField(groupMetaField.query('[name="add_field_button"]')[0], group.option, group.value);
                    }
                });
            } else {
                groupMetaField = _.addMetaField(_.repeater.query('[name="button_type_default"]')[0], value.option, value.value);
            }
        });
    },

    checkTypeSelector: function() {

        var count = this.repeater.query('[name="delete_button"],[name="delete_group_button"]').length
        if(count === 0) {
            this.typeSelector.enable();
        } else {
            this.typeSelector.disable();
        }
    }
});