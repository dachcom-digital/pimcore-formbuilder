pimcore.registerNS('Formbuilder.extjs.components.formTypeBuilder');
Formbuilder.extjs.components.formTypeBuilder = Class.create({

    form: null,
    formIsValid: true,
    formHandler: null,
    type: null,
    typeName: null,
    iconClass: null,
    formTypeTemplates: [],
    configurationLayout: [],
    allowedConstraints: [],
    attributeSelector: null,
    storeData: {},

    initialize: function (formHandler, treeNode, fieldConfig, availableFormFieldTemplates, values) {
        this.formHandler = formHandler;
        this.treeNode = treeNode;
        this.formTypeTemplates = availableFormFieldTemplates;
        this.configurationLayout = fieldConfig.configuration_layout;
        this.allowedConstraints = fieldConfig.constraints;
        this.iconClass = fieldConfig.icon_class;
        this.type = fieldConfig.type;
        this.typeName = fieldConfig.type;
        this.initData(values);
    },

    getType: function () {
        return this.type;
    },

    getName: function () {
        return this.getData().name;
    },

    getDisplayName: function () {
        return this.getData().display_name ? this.getData().display_name : null;
    },

    getTypeName: function () {
        return this.typeName;
    },

    getIconClass: function () {
        return this.iconClass;
    },

    initData: function (values) {

        this.valid = true;

        if (values) {
            this.storeData = values;
        } else {
            this.storeData = {
                type: this.getType()
            };
        }

        this.renderLayout();

    },

    renderLayout: function () {

        var items = [];
        Ext.Array.each(this.configurationLayout, function (tabLayout, i) {
            items.push(new Ext.Panel({
                title: tabLayout.label,
                closable: false,
                autoScroll: true,
                items: [
                    this.getForm(tabLayout.fields, i === 0)
                ]
            }));
        }.bind(this));

        this.form = new Ext.form.Panel({
            items: {
                xtype: 'tabpanel',
                tabPosition: 'top',
                region: 'center',
                deferredRender: true,
                enableTabScroll: true,
                border: false,
                items: items,
                activeTab: 0
            }
        });

        return this.form;
    },

    isValid: function () {
        return this.formIsValid;
    },

    applyData: function () {
        this.formIsValid = this.form.isValid();
        this.storeData = this.transposeFormFields(this.form.getValues());
        this.storeData.type = this.getType();
    },

    getData: function () {
        return this.storeData;
    },

    /**
     * @param formConfig
     * @param isMainTab
     * @returns {*}
     */
    getForm: function (formConfig, isMainTab) {

        var form = this.createBaseForm(isMainTab),
            groupFields = [];

        Ext.Array.each(formConfig, function (fieldSetConfig, i) {

            var fieldSetFields = [],
                fieldConfig,
                field,
                fieldSet = new Ext.form.FieldSet({
                    title: fieldSetConfig.label,
                    collapsible: true,
                    collapsed: fieldSetConfig.collapsed,
                    autoHeight: true,
                    defaultType: 'textfield'
                });

            for (var fieldsIndex = 0; fieldsIndex < fieldSetConfig.fields.length; fieldsIndex++) {
                fieldConfig = fieldSetConfig.fields[fieldsIndex];
                field = this.generateField(fieldConfig);
                if (field !== null) {
                    fieldSetFields.push(field);
                }
            }

            fieldSet.add(fieldSetFields);
            groupFields.push(fieldSet);
        }.bind(this));

        form.add(groupFields);

        return form;

    },

    createBaseForm: function (isMainTab) {

        var _ = this,
            form = new Ext.form.Panel({
                bodyStyle: 'padding: 10px;',
                labelWidth: 150,
                defaultType: 'textfield',
            }),
            templateDefaultValue = undefined,
            parentContainerTrail = [],
            node = this.getTreeNode(),
            nodeData;

        while (node !== null) {
            nodeData = node.getData();
            if (nodeData.hasOwnProperty('fbType') && nodeData.fbType === 'container') {
                if (nodeData.hasOwnProperty('object') && nodeData.object.hasOwnProperty('storeData')) {
                    parentContainerTrail.push(nodeData.object.storeData.name + (nodeData.object.storeData.sub_type === 'repeater' ? '_[?]' : ''));
                }
            }
            node = node && node.hasOwnProperty('parentNode') ? node.parentNode : null;
        }

        if (isMainTab === true) {

            Ext.iterate(this.formTypeTemplates, function (data, value) {
                if (data.default === true) {
                    templateDefaultValue = data.value;
                    return false;
                }
            });

            //create "display name" field.
            form.add({
                xtype: 'textfield',
                fieldLabel: t('form_builder_field_display_name'),
                name: 'display_name',
                value: this.getDisplayName(),
                allowBlank: false,
                anchor: '100%',
                enableKeyEvents: true,
                listeners: {
                    keyup: this.checkFieldDisplayName.bind(this),
                    blur: this.checkFieldLabelName.bind(this)
                }
            });

            form.add({
                xtype: 'panel',
                style: {
                    margin: '0 0 15px 0',
                },
                layout: {
                    type: 'hbox',
                    align: 'left'
                },
                items: [
                    {
                        xtype: 'textfield',
                        width: 300,
                        fieldLabel: t('form_builder_field_name'),
                        name: 'name',
                        value: (this.getName() ? this.getName() : this.generateId()),
                        allowBlank: false,
                        enableKeyEvents: true,
                        validator: function (v) {
                            if (in_array(v.toLowerCase(), _.formHandler.parentPanel.getConfig().forbidden_form_field_names)) {
                                this.setValue('');
                                Ext.MessageBox.alert(t('error'), t('form_builder_forbidden_file_name'));
                                return false;
                            }
                            return new RegExp('^[A-Za-z0-9?_]+$').test(v);
                        },
                        listeners: {
                            keyup: function (field) {
                                var label = field.up('panel').down('label');
                                label.up('panel').down('label').setText((parentContainerTrail.length > 0 ? parentContainerTrail.join('_') + '_' : '') + field.getValue());
                            }.bind(this),
                        }
                    },
                    {
                        xtype: 'label',
                        style: {
                            margin: '7px 0 0 10px',
                            display: 'block',
                            fontStyle: 'italic',
                        },
                        listeners: {
                            render: function (label) {
                                Ext.create('Ext.tip.ToolTip', {
                                    target: label.getEl(),
                                    html: t('form_builder_field_name_preview')
                                });
                            },
                            afterrender: function (field) {
                                var fieldValue = field.up('panel').down('textfield').getValue();
                                field.setText((parentContainerTrail.length > 0 ? parentContainerTrail.join('_') + '_' : '') + fieldValue);
                            }.bind(this),
                        }
                    }
                ],
            });

            //create "template" field
            form.add({
                xtype: 'combo',
                fieldLabel: t('form_builder_field_template'),
                name: 'optional.template',
                value: this.getFieldValue('optional.template') ? this.getFieldValue('optional.template') : templateDefaultValue,
                queryDelay: 0,
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                store: new Ext.data.Store({
                    data: this.formTypeTemplates
                }),
                editable: false,
                triggerAction: 'all',
                anchor: '100%',
                allowBlank: true
            });
        }

        return form;
    },

    generateField: function (fieldConfig) {

        var fields,
            field = null,
            fieldComponent = null,
            formFields = Object.keys(Formbuilder.extjs.form.fields).filter(
                function (value) {
                    return value !== 'abstract';
                }
            ),
            legacyFormFields = typeof Formbuilder.comp.type.config_fields !== 'undefined' ? Object.keys(Formbuilder.comp.type.config_fields).filter(
                function (value) {
                    return value !== 'abstract';
                }) : [];

        fields = Ext.Array.merge(formFields, legacyFormFields);

        if (fields.indexOf(fieldConfig.type) !== -1) {

            // legacy
            if (typeof Formbuilder.comp.type.config_fields[fieldConfig.type] !== 'undefined') {
                fieldComponent = new Formbuilder.comp.type.config_fields[fieldConfig.type]();
            } else {
                fieldComponent = new Formbuilder.extjs.form.fields[fieldConfig.type]()
            }

            field = fieldComponent.getField(
                fieldConfig,
                this.getFieldValue(fieldConfig.id)
            );

            return field;
        }

        new Error('Unrecognized field type ' + fieldConfig.type);
    },

    /**
     * @param field
     */
    checkFieldDisplayName: function (field) {
        if (this.treeNode) {
            this.treeNode.set('text', field.getValue());
        }
    },

    /**
     *
     * @returns {*}
     */
    getTreeNode: function () {
        return this.treeNode;
    },

    /**
     * @param field
     */
    checkFieldLabelName: function (field) {

        var labelField = this.form.queryBy(function (component) {
            return in_array(component.name, ['options.label']);
        });

        if (!labelField[0]) {
            return;
        }

        if (labelField[0].getValue() === '') {
            labelField[0].setValue(field.getValue());
        }
    },

    /**
     * THANKS!
     * https://github.com/Gigzolo/dataobject-parser
     *
     * @param data
     * @returns {{}}
     */
    transposeFormFields: function (data) {
        var transposedData = DataObjectParser.transpose(data);
        return transposedData.data();
    },

    unTransposeFormFields: function (data) {
        var unTransposedData = DataObjectParser.untranspose(data);
        return unTransposedData;
    },

    getFieldValue(id) {
        if (id.indexOf('options.') !== -1) {
            return this.storeData['options'] ? this.storeData['options'][id.replace(/^(options\.)/, '')] : undefined;
        } else if (id.indexOf('optional.') !== -1) {
            return this.storeData['optional'] ? this.storeData['optional'][id.replace(/^(optional\.)/, '')] : undefined;
        } else if (this.storeData[id]) {
            return this.storeData[id];
        }

        return undefined;
    },

    generateId: function () {
        return Ext.id(null, 'field_');
    }
});
