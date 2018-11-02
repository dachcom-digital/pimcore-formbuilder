pimcore.registerNS('Formbuilder.comp.types.keyValueRepeater');
Formbuilder.comp.types.keyValueRepeater = Class.create({

    allowGroupSelector: true,

    fieldConfig: null,

    storeData: null,

    repeater: null,

    type: 'default', //default |grouped

    optionType: 'user', //user | store

    optionStore: null,

    initialize: function (fieldConfig, storeData, optionStore, allowGroup) {

        this.fieldConfig = fieldConfig;
        this.storeData = storeData;
        this.optionStore = optionStore;

        if (this.optionStore) {
            this.optionType = 'store';
        }

        if (allowGroup === false) {
            this.allowGroupSelector = false;
        }

        this.generateRepeaterWithKeyValue();

    },

    getRepeater: function () {
        return this.repeater;
    },

    generateRepeaterWithKeyValue: function () {

        var allowFirstOptionsEmpty = false,
            storeData = [];

        storeData.push(['default', t('form_builder_repeater_default')]);
        if (this.allowGroupSelector === true) {
            storeData.push(['grouped', t('form_builder_repeater_grouped')]);
        }

        this.typeSelector = new Ext.form.ComboBox({
            width: 300,
            triggerAction: 'all',
            submitValue: false,
            store: storeData,
            listeners: {
                select: function (combo, rec) {
                    this.type = combo.getValue();
                    combo.up().up().query('[name^=button_type_]').forEach(function (el) {
                        el.hide()
                    });
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
                    title: '',
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
                    title: '',
                    text: t('form_builder_add_grouped_metadata')
                }
            }
        ];

        if (allowFirstOptionsEmpty) {
            items.unshift({
                xtype: 'panel',
                name: 'multiOptionsInfo',
                fieldLabel: '',
                submitValue: false,
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
            autoHeight: true,
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

    addGroupedMetaField: function (button, value) {

        var _ = this,
            fieldSet = button.up().up(),
            groupFields = this.repeater.query('[name="meta_group"]'),
            fieldSetName = fieldSet.name,
            currentIndex = 0;

        if (groupFields.length > 0) {
            currentIndex = groupFields[groupFields.length - 1].groupIndex + 1;
        }

        var items = [
            {
                xtype: 'textfield',
                text: t('form_builder_repeater_group_name'),
                name: _.generateFieldName(currentIndex, 0, 'name'),
                label: t('group_name'),
                value: typeof value !== 'object' ? value : null,
                cls: 'repeater_group_name_field',
                listeners: {
                    updateIndexName: function (fieldSetIndex, fieldContainerIndex) {
                        var name = _.generateFieldName(fieldSetIndex, 0, 'name');
                        this.name = name;
                    }
                }
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
                    this.updateIndex();
                    this.checkTypeSelector();
                }.bind(this, compositeField)
            }
        ];

        var compositeField = new Ext.form.FieldSet({
            collapsible: false,
            autoHeight: true,
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

    addMetaField: function (button, option, value) {

        var _ = this,
            fieldSet = button.up().up(),
            fieldSetName = fieldSet.name,
            fieldSetIndex = null,
            fieldContainerIndex = 0;

        fieldContainerIndex = fieldSet.query('[name="delete_button"]').length;

        if (this.type === 'grouped') {
            fieldSetIndex = fieldSet.groupIndex;
        }

        var optionField = null;

        if (this.optionType === 'user') {

            optionField = new Ext.form.TextField({
                name: _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'option'),
                fieldLabel: t('form_builder_option'),
                anchor: '100%',
                summaryDisplay: true,
                value: typeof option !== 'object' ? option : null,
                flex: 1,
                margin: '0 10px 0 0',
                listeners: {
                    updateIndexName: function (fieldSetIndex, fieldContainerIndex) {
                        var name = _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'option');
                        this.name = name;
                    }
                }
            });

        } else {

            var optionsStore = new Ext.data.ArrayStore({
                fields: ['label', 'value'],
                data: this.optionStore
            });

            optionField = new Ext.form.ComboBox({
                name: _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'option'),
                fieldLabel: t('form_builder_option'),
                queryDelay: 0,
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                store: optionsStore,
                editable: false,
                triggerAction: 'all',
                anchor: "100%",
                summaryDisplay: true,
                allowBlank: false,
                value: typeof option !== 'object' ? option : null,
                listeners: {
                    updateIndexName: function (fieldSetIndex, fieldContainerIndex) {
                        var name = _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'option');
                        this.name = name;
                    }
                }
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
                    name: _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'value'),
                    fieldLabel: t('form_builder_value'),
                    anchor: '100%',
                    summaryDisplay: true,
                    allowBlank: false,
                    value: typeof value !== 'object' ? value : null,
                    flex: 1,
                    margin: '0 10px 0 0',
                    listeners: {
                        updateIndexName: function (fieldSetIndex, fieldContainerIndex) {
                            var name = _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'value');
                            this.name = name;
                        }
                    }
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
                this.updateIndex();
                this.checkTypeSelector();
            }.bind(this, compositeField)
        }, {
            xtype: 'box',
            style: 'clear:both;'
        }]);

        fieldSet.add(compositeField);
        fieldSet.updateLayout();

        this.checkTypeSelector();

    },

    populateRepeater: function () {

        var _ = this,
            type = null;

        if (!this.storeData || this.storeData.length === 0) {
            return;
        }

        //set selector first.
        Ext.Object.each(this.storeData, function (index, value) {

            if (Ext.isArray(value)) { //meta group
                type = 'grouped';
            } else { //meta default
                type = 'default';
            }

            _.typeSelector.setValue(type);
            _.typeSelector.fireEvent('select', _.typeSelector);
            return false; //break

        });

        Ext.Object.each(this.storeData, function (index, value) {
            var groupMetaField = undefined;
            if (type === 'grouped') {
                Ext.Array.each(value, function (group, index) {
                    if (group.name && index === 0) {
                        groupMetaField = _.addGroupedMetaField(_.repeater.query('[name="button_type_grouped"]')[0], group.name);
                    }
                    if (groupMetaField) {
                        _.addMetaField(groupMetaField.query('[name="add_field_button"]')[0], group.option, group.value);
                    }
                });
            } else {
                groupMetaField = _.addMetaField(_.repeater.query('[name="button_type_default"]')[0], value.option, value.value);
            }
        });
    },

    generateFieldName: function (fieldSetIndex, fieldContainerIndex, name) {
        if (fieldSetIndex === null) {
            return this.fieldConfig.id + '.' + fieldContainerIndex + '.' + name;
        }

        return this.fieldConfig.id + '.' + fieldSetIndex + '.' + fieldContainerIndex + '.' + name;
    },

    updateIndex: function () {
        if (this.type === 'grouped') {
            var fieldSets = Ext.ComponentQuery.query('fieldset', this.repeater);
            Ext.Array.each(fieldSets, function (container, fieldSetIndex) {
                //name field
                var nameFields = Ext.ComponentQuery.query('textfield[cls*=repeater_group_name_field]', container);
                Ext.Array.each(nameFields, function (nameField) {
                    nameField.fireEvent('updateIndexName', fieldSetIndex, 0);
                });
                Ext.Array.each(Ext.ComponentQuery.query('fieldcontainer', container), function (container, fieldContainerIndex) {
                    Ext.Array.each(Ext.ComponentQuery.query('textfield', container), function (field) {
                        field.fireEvent('updateIndexName', fieldSetIndex, fieldContainerIndex);
                    });
                });
            });
        } else {
            var fieldContainer = Ext.ComponentQuery.query('fieldcontainer', this.repeater);
            Ext.Array.each(fieldContainer, function (container, fieldContainerIndex) {
                Ext.Array.each(Ext.ComponentQuery.query('textfield', container), function (field) {
                    field.fireEvent('updateIndexName', null, fieldContainerIndex);
                });
            });
        }
    },

    checkTypeSelector: function () {
        var count = this.repeater.query('[name="delete_button"],[name="delete_group_button"]').length
        if (count === 0) {
            this.typeSelector.enable();
        } else {
            this.typeSelector.disable();
        }
    }
});