pimcore.registerNS('Formbuilder.extjs.components.formFieldConstraint');
Formbuilder.extjs.components.formFieldConstraint = Class.create({

    form: null,
    formIsValid: true,
    formHandler: null,
    type: null,
    typeName: null,
    iconClass: null,
    storeData: {},

    initialize: function (formHandler, treeNode, constraintConfig, values) {
        this.formHandler = formHandler;
        this.treeNode = treeNode;
        this.iconClass = constraintConfig.icon_class;
        this.type = constraintConfig.id;
        this.typeName = constraintConfig.label;
        this.config = constraintConfig.config;
        this.initData(values);
    },

    /**
     *
     * @returns {*}
     */
    getTreeNode: function () {
        return this.treeNode;
    },

    getType: function () {
        return this.type;
    },

    getName: function () {
        return this.type;
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

        var items = [],
            item = new Ext.Panel({
            title: t('form_builder_base'),
            closable: false,
            autoScroll: true,
            items: [
                this.getForm()
            ]

        });

        items.push(item);

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

    transposeFormFields: function (data) {
        var transposedData = DataObjectParser.transpose(data);
        return transposedData.data();
    },

    getForm: function () {
        return this.createBaseForm();
    },

    createBaseForm: function () {

        var configFieldCounter = 0,
            form = new Ext.form.Panel({
                bodyStyle: 'padding: 10px;',
                labelWidth: 150,
                defaultType: 'textfield',
            });

        Ext.Array.each(this.config, function (configElement) {

            var field,
                value = this.getFieldValue(configElement.name, configElement.defaultValue),
                opacityStyle = 'opacity: ' + (value !== configElement.defaultValue ? 1 : 0.6) + ';';

            switch (configElement.type) {
                case 'string':
                    field = new Ext.form.TextField({
                        fieldLabel: configElement.name,
                        name: 'config.' + configElement.name,
                        value: this.getFieldValue(configElement.name, configElement.defaultValue),
                        allowBlank: true,
                        flex: 2,
                        style: opacityStyle,
                        getSubmitValue: function () {
                            if (this.getValue() === '' || this.getValue() === configElement.defaultValue) {
                                return null;
                            }
                            return this.value;
                        },
                        listeners: {
                            change: function (field, newVal) {
                                var defaultValue = configElement.defaultValue === null ? '' : configElement.defaultValue;
                                field.setStyle('opacity', newVal !== defaultValue ? 1 : 0.6);
                            }
                        }
                    });

                    break;

                case 'bool':
                case 'boolean':
                    field = new Ext.form.Checkbox({
                        fieldLabel: configElement.name,
                        name: 'config.' + configElement.name,
                        value: this.getFieldValue(configElement.name, configElement.defaultValue),
                        checked: false,
                        uncheckedValue: false,
                        inputValue: true,
                        flex: 2,
                        style: opacityStyle,
                        getSubmitValue: function () {
                            if (this.getValue() === configElement.defaultValue) {
                                return null;
                            }
                            return this.value;
                        },
                        listeners: {
                            change: function (field, newVal, oldVal) {
                                field.setStyle('opacity', newVal !== configElement.defaultValue ? 1 : 0.6);
                            }
                        }
                    });

                    break;

                case 'int':
                case 'integer':
                    field = new Ext.form.field.Number({
                        fieldLabel: configElement.name,
                        name: 'config.' + configElement.name,
                        value: this.getFieldValue(configElement.name, configElement.defaultValue),
                        allowDecimals: false,
                        flex: 2,
                        style: opacityStyle,
                        getSubmitValue: function () {
                            if (this.getValue() === configElement.defaultValue) {
                                return null;
                            }
                            return this.value;
                        },
                        listeners: {
                            change: function (field, newVal, oldVal) {
                                field.setStyle('opacity', newVal !== configElement.defaultValue ? 1 : 0.6);
                            }
                        }
                    });

                    break;

                case 'array':
                    field = new Ext.form.field.Tag({
                        fieldLabel: configElement.name,
                        name: 'config.' + configElement.name,
                        value: this.getFieldValue(configElement.name, configElement.defaultValue),
                        flex: 2,
                        queryDelay: 0,
                        mode: 'local',
                        displayField: 'name',
                        valueField: 'index',
                        store: new Ext.data.ArrayStore({
                            fields: ['index', 'name'],
                            data: []
                        }),
                        allowBlank: true,
                        editable: true,
                        hideTrigger: true,
                        filterPickList: false,
                        createNewOnBlur: true,
                        createNewOnEnter: true,
                        selectOnFocus: false,
                        forceSelection: true,
                        style: opacityStyle,
                        getSubmitValue: function () {

                            if (Ext.isArray(this.getValue()) && this.getValue().length === 0) {
                                return null;
                            }

                            if (this.getValue() === configElement.defaultValue) {
                                return null;
                            }

                            return this.value;
                        },
                        listeners: {
                            change: function (field, newVal, oldVal) {

                                if (Ext.isArray(this.getValue()) && this.getValue().length === 0) {
                                    newVal = null;
                                }

                                field.setStyle('opacity', newVal !== configElement.defaultValue ? 1 : 0.6);
                            }
                        }
                    });

                    break;
            }

            if (field) {

                configFieldCounter++;

                var fieldContainer = new Ext.form.FieldContainer({
                    layout: 'hbox',
                    hideLabel: true,
                    style: 'padding-bottom:5px;',
                    items: [
                        field,
                        {
                            xtype: 'button',
                            tooltip: t('reset'),
                            iconCls: 'pimcore_icon_cancel',
                            style: 'background-color:white; border-color:transparent;',
                            handler: function () {
                                field.setValue(configElement.defaultValue);
                            }
                        }
                    ]
                });

                form.add(fieldContainer);

                if (configElement.defaultValue !== undefined) {
                    var defaultValue = configElement.defaultValue;
                    if (typeof(defaultValue) === 'boolean') {
                        defaultValue = defaultValue === true ? 'true' : 'false';
                    }

                    var description = t('form_builder_constraint_default_value') + ': <code>' + defaultValue + '</code>';
                    if (Ext.isString(configElement.name) && configElement.name.toLowerCase().indexOf('message') !== -1) {
                        description += '<br>' + t('form_builder_constraint_message_note');
                    }

                    form.add(new Ext.form.Label({
                        name: 'defaultValue',
                        html: description,
                        style: {
                            padding: '2px 5px',
                            margin: '0 0 15px 0',
                            display: 'block',
                            fontSize: '11px',
                            background: '#f5f5f5',
                            width: '100%'
                        },
                        anchor: '100%'
                    }));
                }
            }

        }.bind(this));

        if (configFieldCounter === 0) {
            form.add(new Ext.form.Label({
                name: 'label',
                text: 'Nothing to do so far. Just enjoy this fancy constraint.',
                style: {
                    padding: '10px 0 0 0',
                    width: '100%'
                },
                anchor: '100%'
            }));
        }

        return form;

    },

    getFieldValue: function (id, defaultValue) {
        if (!this.storeData['config'] || typeof(this.storeData['config'][id]) === 'undefined') {
            return defaultValue;
        }

        return this.storeData['config'][id];
    }
});