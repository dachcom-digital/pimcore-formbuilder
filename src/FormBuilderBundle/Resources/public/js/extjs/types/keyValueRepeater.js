pimcore.registerNS('Formbuilder.extjs.types.keyValueRepeater');
Formbuilder.extjs.types.keyValueRepeater = Class.create({

    allowGroupSelector: true,
    allowChoiceMeta: true,
    allowSort: true,

    fieldIdentifier: null,

    fieldLabel: null,

    storeData: null,

    repeater: null,

    type: 'default', //default |grouped

    optionType: 'user', //user | store

    optionStore: null,

    initialize: function (identifier, label, storeData, optionStore, allowGroup, allowChoiceMeta, allowSort) {

        this.fieldIdentifier = identifier;
        this.fieldLabel = label;
        this.storeData = storeData;
        this.optionStore = optionStore;

        if (this.optionStore) {
            this.optionType = 'store';
        }

        if (allowGroup === false) {
            this.allowGroupSelector = false;
        }

        if (allowChoiceMeta === false) {
            this.allowChoiceMeta = false;
        }

        if (allowSort === false) {
            this.allowSort = false;
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
            name: '_keyValueRepeaterTypeSelector',
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
            title: this.fieldLabel,
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

    addMetaField: function (button, option, value, choiceMetaData) {

        var _ = this,
            fieldSet = button.up().up(),
            fieldSetIndex = null,
            fieldContainerIndex,
            optionField;

        fieldContainerIndex = fieldSet.query('[name="delete_button"]').length;

        if (this.type === 'grouped') {
            fieldSetIndex = fieldSet.groupIndex;
        }

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
                        this.name = _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'option');
                    }
                }
            });

        } else {

            optionField = new Ext.form.ComboBox({
                name: _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'option'),
                fieldLabel: t('form_builder_option'),
                queryDelay: 0,
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                store: new Ext.data.ArrayStore({
                    fields: ['label', 'value'],
                    data: this.optionStore
                }),
                editable: false,
                triggerAction: 'all',
                anchor: "100%",
                summaryDisplay: true,
                allowBlank: false,
                value: typeof option !== 'object' ? option : null,
                listeners: {
                    updateIndexName: function (fieldSetIndex, fieldContainerIndex) {
                        this.name = _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'option');
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
                            this.name = _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'value');
                        }
                    }
                },
                {
                    xtype: 'hidden',
                    name: _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'choice_meta'),
                    value: choiceMetaData,
                    cls: 'choice_meta_data',
                    listeners: {
                        updateIndexName: function (fieldSetIndex, fieldContainerIndex) {
                            this.name = _.generateFieldName(fieldSetIndex, fieldContainerIndex, 'choice_meta');
                        }
                    }
                },
            ]
        });

        if (this.allowChoiceMeta === true) {
            this.addChoiceMeta(compositeField, choiceMetaData);
        }

        if (this.allowSort === true) {
            this.addSortBtn(compositeField, fieldSet);
        }

        compositeField.add({
            xtype: 'button',
            iconCls: 'pimcore_icon_delete',
            style: 'float:left;',
            name: 'delete_button',
            handler: function (compositeField) {
                fieldSet.remove(compositeField);
                fieldSet.updateLayout();
                this.updateIndex();
                this.checkTypeSelector();
            }.bind(this, compositeField)
        });

        compositeField.add({
            xtype: 'box',
            style: 'clear:both;'
        });

        fieldSet.add(compositeField);
        fieldSet.updateLayout();

        this.checkTypeSelector();

    },

    addChoiceMeta: function (compositeField, choiceMetaData) {

        var metaValues = choiceMetaData ? Ext.decode(choiceMetaData) : null;

        compositeField.add({
            xtype: 'button',
            iconCls: 'pimcore_icon_settings',
            name: 'choice_meta_button',
            style: 'float:left;',
            handler: function () {

                var transposedHrefFieldValues = null,
                    hrefFieldValues = null,
                    hrefFieldConfig = {
                        label: t('form_builder_type_field.choices.relation'),
                        id: 'relation',
                        config: {
                            types: ['document', 'asset', 'object'],
                            subtypes: {}
                        }
                    }, hrefField = new Formbuilder.extjs.form.fields.href();

                if (Ext.isObject(metaValues)) {
                    transposedHrefFieldValues = DataObjectParser.transpose(metaValues).data();
                    if (Ext.isObject(transposedHrefFieldValues) && transposedHrefFieldValues.hasOwnProperty('relation')) {
                        hrefFieldValues = transposedHrefFieldValues.relation;
                    }
                }

                var metaWindow = new Ext.Window({
                    width: 600,
                    height: 400,
                    iconCls: 'pimcore_icon_settings',
                    title: t('form_builder_type_field.choices.meta'),
                    layout: 'form',
                    closeAction: 'close',
                    plain: true,
                    autoScroll: true,
                    modal: false,
                    items: [
                        {
                            xtype: 'form',
                            flex: 1,
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: t('form_builder_type_field.choices.tooltip'),
                                    name: 'tooltip',
                                    width: 300,
                                    value: metaValues !== null ? metaValues.tooltip : null,
                                    allowBlank: true,
                                    required: false
                                },
                                hrefField.getField(hrefFieldConfig, hrefFieldValues),
                            ]
                        }
                    ],
                    buttons: [
                        {
                            text: t('save'),
                            iconCls: 'pimcore_icon_save',
                            handler: function (button) {
                                var choiceMetaData = Ext.ComponentQuery.query('hidden[cls~="choice_meta_data"]', compositeField),
                                    formValues = button.up('window').down('form').getForm().getValues();

                                if (choiceMetaData.length > 0) {
                                    choiceMetaData[0].setValue(Ext.encode(formValues));
                                    metaValues = formValues;
                                }

                                metaWindow.hide();
                                metaWindow.destroy();
                            }
                        },
                        {
                            text: t('cancel'),
                            iconCls: 'pimcore_icon_cancel',
                            handler: function () {
                                metaWindow.hide();
                                metaWindow.destroy();
                            }.bind(this)
                        }
                    ]
                });

                metaWindow.show();

            }.bind(this, compositeField)
        });
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
            var groupMetaField = undefined,
                choiceMeta = null;
            if (type === 'grouped') {
                Ext.Array.each(value, function (group, index) {
                    var choiceMeta = null;
                    if (group.name && index === 0) {
                        groupMetaField = _.addGroupedMetaField(_.repeater.query('[name="button_type_grouped"]')[0], group.name);
                    }
                    if (groupMetaField) {
                        choiceMeta = group.hasOwnProperty('choice_meta') ? group.choice_meta : null;
                        _.addMetaField(groupMetaField.query('[name="add_field_button"]')[0], group.option, group.value, choiceMeta);
                    }
                });
            } else {
                choiceMeta = value.hasOwnProperty('choice_meta') ? value.choice_meta : null;
                groupMetaField = _.addMetaField(_.repeater.query('[name="button_type_default"]')[0], value.option, value.value, choiceMeta);
            }
        });
    },

    generateFieldName: function (fieldSetIndex, fieldContainerIndex, name) {
        if (fieldSetIndex === null) {
            return this.fieldIdentifier + '.' + fieldContainerIndex + '.' + name;
        }

        return this.fieldIdentifier + '.' + fieldSetIndex + '.' + fieldContainerIndex + '.' + name;
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
                    Ext.Array.each(Ext.ComponentQuery.query('hidden', container), function (field) {
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
                Ext.Array.each(Ext.ComponentQuery.query('hidden', container), function (field) {
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
    },

    addSortBtn: function (compositeField, fieldSet) {
        const changeOrder = (item, indexChange, limit) => {
            // get index from fieldset items
            const oldIndex = fieldSet.items.indexOf(item);
            // increase or reduce newIndex by one
            const newIndex = oldIndex + indexChange;

            // do nothing if newIndex is reaching limit
            if (newIndex === limit) {
                return;
            }

            // swap selected field and item at new index
            let oldItem = fieldSet.items.getAt(newIndex);
            fieldSet.items.insert(newIndex, item);
            fieldSet.items.insert(oldIndex, oldItem);

            fieldSet.updateLayout();
            this.updateIndex();
        }

        compositeField.add({
            xtype: 'button',
            iconCls: 'pimcore_icon_up',
            style: 'float:left;',
            name: 'sort_up_button',
            handler: function (compositeField) {
                changeOrder(compositeField, -1, 0);
            }.bind(this, compositeField)
        });
        compositeField.add({
            xtype: 'button',
            iconCls: 'pimcore_icon_down',
            style: 'float:left;',
            name: 'sort_down_button',
            handler: function (compositeField) {
                changeOrder(compositeField, 1, fieldSet.items.length);
            }.bind(this, compositeField)
        });
    },

});
