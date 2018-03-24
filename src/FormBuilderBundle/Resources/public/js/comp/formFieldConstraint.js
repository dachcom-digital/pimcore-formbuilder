pimcore.registerNS('Formbuilder.comp.type.formFieldConstraint');
Formbuilder.comp.type.formFieldConstraint = Class.create({

    form: null,

    formIsValid: true,

    formHandler: null,

    type: null,

    typeName: null,

    iconClass: null,

    storeData: {},

    initialize: function (formHandler, treeNode, constraint, values) {

        this.formHandler = formHandler;
        this.treeNode = treeNode;
        this.iconClass = constraint.icon_class;
        this.type = constraint.id;
        this.typeName = constraint.label;
        this.config = constraint.config;

        this.initData(values);

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

        var items = [];

        var item = new Ext.Panel({
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

        var form = this.createBaseForm();
        return form;

    },

    createBaseForm: function () {

        var _ = this,
            configFieldCounter = 0,
            form = new Ext.form.Panel({
                bodyStyle: 'padding: 10px;',
                labelWidth: 150,
                defaultType: 'textfield',
            });

        Ext.Array.each(this.config, function (configElement) {

            var field;

            switch (configElement.type) {

                case 'string':
                    field = new Ext.form.TextField({
                        fieldLabel: configElement.name,
                        name: 'config.' + configElement.name,
                        value: this.getFieldValue(configElement.name, configElement.defaultValue),
                        allowBlank: true,
                        anchor: '100%',
                    });

                    break;

                case 'boolean':
                    field = new Ext.form.Checkbox({
                        fieldLabel: configElement.name,
                        name: 'config.' + configElement.name,
                        checked: false,
                        uncheckedValue: false,
                        inputValue: true,
                        value: this.getFieldValue(configElement.name, configElement.defaultValue)
                    });

                    break;

                case 'integer':
                    field = new Ext.form.field.Number({
                        fieldLabel: configElement.name,
                        name: 'config.' + configElement.name,
                        allowDecimals: false,
                        anchor: '100%',
                        value: this.getFieldValue(configElement.name, configElement.defaultValue)
                    });

                    break;
            }

            if (field) {
                configFieldCounter++;
                form.add(field);

                if (configElement.defaultValue !== undefined) {
                    var defaultValue = configElement.defaultValue;
                    if (typeof(defaultValue) === 'boolean') {
                        defaultValue = defaultValue === true ? 'true' : 'false';
                    }

                    form.add(new Ext.form.Label({
                        name: 'defaultValue',
                        html: t('form_builder_constraint_default_value') + ': <code>' + defaultValue + '</code>',
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
        if(!this.storeData['config'] || typeof(this.storeData['config'][id]) === 'undefined') {
            return defaultValue;
        }

        return this.storeData['config'][id];
    }
});