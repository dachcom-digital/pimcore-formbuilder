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

    attributeSelector: null,

    storeData : {},

    forbiddenFieldNames: [
        'abstract','class','data','folder','list','permissions','resource','concrete','interface','service', 'fieldcollection', 'localizedfield', 'objectbrick'
    ],

    initialize: function(formHandler, treeNode, initData, availableFormFieldTemplates, values) {

        this.formHandler = formHandler;
        this.treeNode = treeNode;
        this.formTypeTemplates = availableFormFieldTemplates;
        this.configurationLayout = initData.configuration_layout;

        this.iconClass = initData.icon_class;
        this.type = initData.type;
        this.typeName = initData.text;

        this.initData(values);

    },

    getType: function() {
        return this.type;
    },

    getName: function() {
        return this.getData().name;
    },

    getTypeName: function() {
        return this.typeName;
    },

    getIconClass: function() {
        return this.iconClass;
    },

    initData: function(values) {

        this.valid = true;

        if(values) {
            this.storeData = values;
        } else {
            this.storeData = {
                type: this.getType()
            };
        }

        this.renderLayout();

    },

    renderLayout: function() {

        var items = [];

        for (var i = 0; i < this.configurationLayout.length; i++) {

            var tabLayout = this.configurationLayout[i];

            var item = new Ext.Panel({
                title: tabLayout.label,
                closable: false,
                autoScroll: true,
                items: [
                    this.getForm(tabLayout.fields, i === 0)
                ]

            });

            items.push(item);

        }

        this.form = new Ext.form.Panel({
            items: {
                xtype: 'tabpanel',
                tabPosition: 'top',
                region:'center',
                deferredRender: true,
                enableTabScroll: true,
                border: false,
                items: items,
                activeTab: 0
            }
        });

        this.form.on('render', this.layoutRendered.bind(this));

        return this.form;
    },

    layoutRendered: function() {

        var items = this.form.queryBy(function(component) {
            return in_array(component.name, ['display_name']);
        });

        for (var i = 0; i < items.length; i++) {
            if (items[i].name === 'display_name') {
                items[i].on('keyup', this.checkFieldDisplayName.bind(this));
                items[i].on('blur', this.checkFieldLabelName.bind(this));
            }
        }
    },

    isValid: function() {
        return this.formIsValid;
    },

    applyData: function() {

        this.formIsValid = this.form.isValid();
        this.storeData =  this.transposeFormFields(this.form.getValues());
        this.storeData.type = this.getType();

    },

    getData: function() {
        return this.storeData;
    },

    getForm: function(formConfig, isMainTab) {

        var form = this.createBaseForm(isMainTab);

        var groupFields = [];
        for (var i = 0; i < formConfig.length; i++) {

            var fieldSetConfig = formConfig[i],
                fieldSet = new Ext.form.FieldSet({
                    title: fieldSetConfig.label,
                    collapsible: true,
                    collapsed: fieldSetConfig.collapsed,
                    autoHeight :true,
                    defaultType: 'textfield'
                });

            var fieldSetFields = [];
            for (var fieldsIndex = 0; fieldsIndex < fieldSetConfig.fields.length; fieldsIndex++) {

                var fieldConfig = fieldSetConfig.fields[fieldsIndex],
                    field = this.generateField(fieldConfig);

                if(field !== null) {
                    fieldSetFields.push(field);
                }
            }

            fieldSet.add(fieldSetFields);

            groupFields.push(fieldSet);

        }

        form.add(groupFields);

        return form;

    },

    createBaseForm: function(isMainTab) {

        var _ = this,
            form = new Ext.form.Panel({
                bodyStyle: 'padding: 10px;',
                labelWidth: 150,
                defaultType: 'textfield',
            });

        if(isMainTab === true) {

            //create "display name" field.
            var displayNameValue = this.getFieldValue('display_name');
            form.add(new Ext.form.TextField({
                fieldLabel: t('form_builder_field_display_name'),
                name: 'display_name',
                value: displayNameValue ? displayNameValue : this.typeName,
                allowBlank: false,
                anchor: '100%',
                enableKeyEvents: true
            }));

            //create "name" field.
            var nameValue = this.getFieldValue('name');
            form.add(new Ext.form.TextField({
                fieldLabel:  t('form_builder_field_name'),
                name: 'name',
                value: nameValue ? nameValue : this.generateUniqueFieldName(),
                allowBlank: false,
                anchor: '100%',
                enableKeyEvents: true,
                validator: function(v) {
                    if(in_array(v.toLowerCase(), _.forbiddenFieldNames)) {
                        this.setValue('');
                        Ext.MessageBox.alert(t('error'), t('form_builder_forbidden_file_name'));
                        return false;
                    }
                    return new RegExp('^[A-Za-z0-9?_]+$').test(v);
                }
            }));

            var templateSelectStore = new Ext.data.Store({
                data : this.formTypeTemplates
            });

            var templateDefaultValue = undefined;
            Ext.iterate(this.formTypeTemplates, function(data, value) {
                if(data.default === true) {
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

    generateField: function(fieldConfig) {

        var field = null;

        switch(fieldConfig.type) {

            case 'label':

                field = new Ext.form.Label({
                    style: 'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
                    text: fieldConfig.label
                });

                break;

            case 'tagfield':

                var tagStore = new Ext.data.ArrayStore({
                    fields: ['elements'],
                    data : fieldConfig.options
                });

                field = new Ext.form.field.Tag({
                    name: fieldConfig.id,
                    fieldLabel: fieldConfig.label,
                    store: tagStore,
                    value: this.getFieldValue(fieldConfig.id),
                    createNewOnEnter: true,
                    createNewOnBlur: true,
                    filterPickList: true,
                    queryMode: 'elements',
                    displayField: 'elements',
                    valueField: 'elements',
                    hideTrigger: true,
                    anchor: '100%'
                });

                break;

            case 'numberfield':

                field = new Ext.form.field.Number({
                    name: fieldConfig.id,
                    fieldLabel: fieldConfig.label,
                    allowDecimals: false,
                    anchor: '100%',
                    value: this.getFieldValue(fieldConfig.id)
                });

                break;

            case 'checkbox':

                field = new Ext.form.Checkbox({
                    fieldLabel: fieldConfig.label,
                    name: fieldConfig.id,
                    checked: false,
                    uncheckedValue: false,
                    inputValue: true,
                    value: this.getFieldValue(fieldConfig.id)
                });

                break;

            case 'textfield':

                field = new Ext.form.TextField({
                    fieldLabel: fieldConfig.label,
                    name: fieldConfig.id,
                    value: this.getFieldValue(fieldConfig.id),
                    allowBlank: true,
                    anchor: '100%',
                    enableKeyEvents: true
                });

                break;

            case 'select' :

                var selectStore = new Ext.data.ArrayStore({
                    fields: ['label','value'],
                    data : fieldConfig.config.options
                });

                field = new Ext.form.ComboBox({
                    fieldLabel: fieldConfig.label,
                    name: fieldConfig.id,
                    value: this.getFieldValue(fieldConfig.id),
                    queryDelay: 0,
                    displayField: 'label',
                    valueField: 'value',
                    mode: 'local',
                    store: selectStore,
                    editable: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    allowBlank: true
                });

                break;

            case 'key_value_repeater' :
                field = this.getRepeaterWithKeyValue(fieldConfig);
                break;

            case 'options_repeater' :
                field = this.getRepeaterWithOptions(fieldConfig);
                break;
        }

        return field;

    },

    getRepeaterWithKeyValue: function(fieldConfig) {

        var keyValueRepeater = new Formbuilder.comp.types.keyValueRepeater(
            fieldConfig,
            this.getFieldValue(fieldConfig.id)
        );

        return keyValueRepeater.getRepeater();

    },

    getRepeaterWithOptions: function(fieldConfig) {

        var keyValueRepeater = new Formbuilder.comp.types.keyValueRepeater(
            fieldConfig,
            this.getFieldValue(fieldConfig.id),
            fieldConfig.config.options
        );

        return keyValueRepeater.getRepeater();

    },

    /**
     * @returns {String}
     */
    generateUniqueFieldName: function() {
        return Ext.id(null, 'field_');
    },

    /**
     * @param field
     */
    checkFieldDisplayName: function(field) {
        if (this.treeNode) {
            this.treeNode.set('text', field.getValue());
        }
    },

    /**
     * @param field
     */
    checkFieldLabelName: function(field) {

        var labelField = this.form.queryBy(function (component) {
            return in_array(component.name, ['options.label']);
        });

        if(!labelField[0]) {
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
    checkPath: function(path, field) {

            if( path === '' ) {
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
    pathChecked: function(response) {

        //maybe layout is not available anymore => return!
        if( this.el === null ) {
            return;
        }

        var ret = Ext.decode(response.responseText);

        if(ret.success === true) {
            this.clearInvalid();
        } else {
            this.markInvalid( t('form_builder_path_does_not_exists') );
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

    getFieldValue(id)
    {
        if(id.indexOf('options.') !== -1) {
            return this.storeData['options'][id.replace(/^(options\.)/,'')];
        } else if(id.indexOf('optional.') !== -1) {
            return this.storeData['optional'][id.replace(/^(optional\.)/,'')];
        } else if(this.storeData[id]) {
            return this.storeData[id];
        }

        return undefined;
    }
});