pimcore.registerNS('Formbuilder.extjs.components.formFieldContainer');
Formbuilder.extjs.components.formFieldContainer = Class.create({

    form: null,
    formIsValid: true,
    formHandler: null,
    type: 'container',
    displayName: null,
    subType: null,
    typeName: null,
    iconClass: null,
    containerTemplates: [],
    storeData: {},

    initialize: function (formHandler, treeNode, containerConfig, availableContainerTemplates, values) {
        this.formHandler = formHandler;
        this.treeNode = treeNode;
        this.iconClass = containerConfig.icon_class;
        this.subType = containerConfig.id;
        this.typeName = containerConfig.label;
        this.config = containerConfig.configuration;
        this.containerTemplates = availableContainerTemplates;
        // we don't have a real display name field, so define it here.
        this.displayName = values ? values.display_name : null;
        this.initData(values);
    },

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
        return this.getData().name;
    },

    getDisplayName: function () {
        return this.displayName ? this.displayName : this.typeName;
    },

    updateDisplayName: function (name) {
        this.displayName = this.getSubType().charAt(0).toUpperCase() + this.getSubType().slice(1) + ' (' + name + ')';
        if (this.treeNode) {
            this.treeNode.set('text', this.displayName);
        }
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
        this.storeData.display_name = this.getDisplayName();
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

    getTemplateStore: function () {
        return new Ext.data.Store({
            data: this.containerTemplates
        });
    },

    createBaseForm: function () {

        var _ = this,
            configFieldCounter = 0,
            defaultTemplate = undefined,
            form = new Ext.form.Panel({
                bodyStyle: 'padding: 10px;',
                labelWidth: 150,
                defaultType: 'textfield'
            });

        form.add(new Ext.form.TextField({
            fieldLabel: 'Name',
            name: 'name',
            value: (this.getName() ? this.getName() : this.generateId()),
            allowBlank: false,
            enableKeyEvents: true,
            anchor: '100%',
            listeners: {
                render: function (field) {
                    this.updateDisplayName(field.getValue());
                }.bind(this),
                keyup: function (field) {
                    this.updateDisplayName(field.getValue());
                }.bind(this)
            },
            validator: function (v) {
                var containerInvalidNames = ['group'];
                if (in_array(v.toLowerCase(), containerInvalidNames) ||
                    in_array(v.toLowerCase(), _.formHandler.parentPanel.getConfig().forbidden_form_field_names)) {
                    this.setValue('');
                    Ext.MessageBox.alert(t('error'), t('form_builder_forbidden_file_name'));
                    return false;
                }
                return new RegExp('^[A-Za-z0-9?_]+$').test(v);
            }
        }));

        Ext.iterate(this.containerTemplates, function (data, value) {
            if (data.default === true) {
                defaultTemplate = data.value;
                return false;
            }
        });

        form.add(new Ext.form.ComboBox({
            fieldLabel: t('form_builder_field_template'),
            name: 'configuration.template',
            value: this.getFieldValue('template') ? this.getFieldValue('template') : defaultTemplate,
            queryDelay: 0,
            displayField: 'label',
            valueField: 'value',
            mode: 'local',
            store: this.getTemplateStore(),
            editable: false,
            triggerAction: 'all',
            anchor: '100%',
            allowBlank: true
        }));

        Ext.Array.each(this.config, function (configElement) {
            var field;
            switch (configElement.type) {
                case 'string':
                    field = new Ext.form.TextField({
                        fieldLabel: configElement.label,
                        name: 'configuration.' + configElement.name,
                        value: this.getFieldValue(configElement.name),
                        allowBlank: true,
                        anchor: '100%'
                    });
                    break;
                case 'boolean':
                    field = new Ext.form.Checkbox({
                        fieldLabel: configElement.label,
                        name: 'configuration.' + configElement.name,
                        value: this.getFieldValue(configElement.name),
                        checked: false,
                        uncheckedValue: false,
                        inputValue: true,
                        anchor: '100%'
                    });
                    break;
                case 'integer':
                    field = new Ext.form.field.Number({
                        fieldLabel: configElement.label,
                        name: 'configuration.' + configElement.name,
                        value: this.getFieldValue(configElement.name),
                        allowDecimals: false,
                        anchor: '100%'
                    });
                    break;
                case 'options_repeater' :
                    var keyValueRepeater = new Formbuilder.extjs.types.keyValueRepeater(
                        'configuration.' + configElement.name,
                        configElement.label,
                        this.getFieldValue(configElement.name),
                        configElement.config.options,
                        false
                    );
                    field = keyValueRepeater.getRepeater();
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
        if (!this.storeData['configuration'] || typeof (this.storeData['configuration'][id]) === 'undefined') {
            return null;
        }

        return this.storeData['configuration'][id];
    },

    generateId: function () {
        return Ext.id(null, 'container_');
    }
});