pimcore.registerNS('Formbuilder.comp.type.formFieldContainer');
Formbuilder.comp.type.formFieldContainer = Class.create({

    form: null,
    formIsValid: true,
    formHandler: null,
    type: 'container',
    name: null,
    subType: null,
    typeName: null,
    iconClass: null,
    storeData: {},

    initialize: function (formHandler, treeNode, container, values) {
        this.formHandler = formHandler;
        this.treeNode = treeNode;
        this.iconClass = container.icon_class;
        this.subType = container.id;
        this.typeName = container.label;
        this.name = container.name;
        this.config = container.config;
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

    getSubType: function () {
        return this.subType;
    },

    getName: function () {
        return this.name ? this.name : (this.storeData['name'] ? this.storeData['name'] : null);
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
                type: this.getSubType()
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
        this.storeData.sub_type = this.getSubType();
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

        form.add(new Ext.form.TextField({
            fieldLabel: 'Name',
            name: 'name',
            value: (this.getName() ? this.getName() : Ext.id(null, 'container_')),
            allowBlank: false,
            flex: 2,
            readOnly: true
        }));

        Ext.Array.each(this.config, function (configElement) {
            var field;
            switch (configElement.type) {
                case 'string':
                    field = new Ext.form.TextField({
                        fieldLabel: configElement.name,
                        name: 'configuration.' + configElement.name,
                        value: this.getFieldValue(configElement.name),
                        allowBlank: true,
                        flex: 2,
                        getSubmitValue: function () {
                            if (this.getValue() === '') {
                                return null;
                            }
                            return this.value;
                        }
                    });
                    break;
                case 'boolean':
                    field = new Ext.form.Checkbox({
                        fieldLabel: configElement.name,
                        name: 'configuration.' + configElement.name,
                        value: this.getFieldValue(configElement.name),
                        checked: false,
                        uncheckedValue: false,
                        inputValue: true,
                        flex: 2,
                        getSubmitValue: function () {
                            return this.value;
                        }
                    });
                    break;
                case 'integer':
                    field = new Ext.form.field.Number({
                        fieldLabel: configElement.name,
                        name: 'configuration.' + configElement.name,
                        value: this.getFieldValue(configElement.name),
                        allowDecimals: false,
                        flex: 2,
                        getSubmitValue: function () {
                            return this.value;
                        }
                    });
                    break;
            }

            if (field) {
                configFieldCounter++;
                form.add(field);
            }

        }.bind(this));

        if (configFieldCounter === 0) {
            form.add(new Ext.form.Label({
                name: 'label',
                text: 'Nothing to do so far. Just enjoy this fancy container.',
                style: {
                    padding: '10px 0 0 0',
                    width: '100%'
                },
                anchor: '100%'
            }));
        }

        return form;

    },

    getFieldValue: function (id) {
        if (!this.storeData['configuration'] || typeof(this.storeData['configuration'][id]) === 'undefined') {
            return null;
        }

        return this.storeData['configuration'][id];
    }
});