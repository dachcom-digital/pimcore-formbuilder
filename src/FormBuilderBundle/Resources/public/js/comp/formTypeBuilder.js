pimcore.registerNS('Formbuilder.comp.type.formTypeBuilder');
Formbuilder.comp.type.formTypeBuilder = Class.create({

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

    /**
     *
     * @returns {boolean}
     */
    isValid: function () {
        return this.formIsValid;
    },

    applyData: function () {
        this.formIsValid = this.form.isValid();
        this.storeData = this.transposeFormFields(this.form.getValues());
        this.storeData.type = this.getType();
    },

    /**
     *
     * @returns {Formbuilder.comp.type.formTypeBuilder.storeData|{}}
     */
    getData: function () {
        return this.storeData;
    },

    /**
     *
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
            });

        if (isMainTab === true) {

            //create "display name" field.
            form.add(new Ext.form.TextField({
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
            }));

            //create "name" field.
            form.add(new Ext.form.TextField({
                fieldLabel: t('form_builder_field_name'),
                name: 'name',
                value: (this.getName() ? this.getName() : this.generateId()),
                allowBlank: false,
                anchor: '100%',
                enableKeyEvents: true,
                validator: function (v) {
                    if (in_array(v.toLowerCase(), _.formHandler.parentPanel.getConfig().forbidden_form_field_names)) {
                        this.setValue('');
                        Ext.MessageBox.alert(t('error'), t('form_builder_forbidden_file_name'));
                        return false;
                    }
                    return new RegExp('^[A-Za-z0-9?_]+$').test(v);
                }
            }));

            var templateSelectStore = new Ext.data.Store({
                data: this.formTypeTemplates
            });

            var templateDefaultValue = undefined;
            Ext.iterate(this.formTypeTemplates, function (data, value) {
                if (data.default === true) {
                    templateDefaultValue = data.value;
                    return false;
                }
            });

            //create "template" field
            var templateValue = this.getFieldValue('optional.template');
            form.add(new Ext.form.ComboBox({
                fieldLabel: t('form_builder_field_template'),
                name: 'optional.template',
                value: templateValue ? templateValue : templateDefaultValue,
                queryDelay: 0,
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                store: templateSelectStore,
                editable: false,
                triggerAction: 'all',
                anchor: '100%',
                allowBlank: true
            }));
        }

        return form;

    },

    generateField: function (fieldConfig) {

        var field = null,
            fields = Object.keys(Formbuilder.comp.type.config_fields).filter(
            function(value) {return value !== 'abstract';}
            );

        if (fields.indexOf(fieldConfig.type) !== -1) {
            field = new Formbuilder.comp.type.config_fields[fieldConfig.type]().getField(
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
     *
     * @param path
     * @param field
     */
    checkPath: function (path, field) {

        if (path === '') {
            return;
        }

        Ext.Ajax.request({
            url: '/admin/formbuilder/settings/check-path',
            method: 'post',
            params: {
                path: path
            },
            success: this.pathChecked.bind(field)
        });

    },

    /**
     * @param response
     */
    pathChecked: function (response) {

        //maybe layout is not available anymore => return!
        if (this.el === null) {
            return;
        }

        var ret = Ext.decode(response.responseText);

        if (ret.success === true) {
            this.clearInvalid();
        } else {
            this.markInvalid(t('form_builder_path_does_not_exists'));
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
