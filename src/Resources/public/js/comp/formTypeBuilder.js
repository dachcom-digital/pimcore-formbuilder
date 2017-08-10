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

    showTranslationTab: true,

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

        var storeData = {},
            formValues = this.form.getValues();

        Ext.Object.each(formValues, function(key, value) {

            if (key.substring(0, 10) === 'repeater__') {

                var keys = key.split('__');

                var name = keys[1],
                    index = parseInt(keys[2]),
                    data = keys[3]; //'option'|'value'

                //skip value since its already stored in option loop.
                if(data === 'value') {
                    return;
                }

                var fieldValue = formValues['repeater__' + name + '__' + index + '__value'];

                if(!storeData[name]) {
                    storeData[name] = [];
                }

                storeData[name].push({
                    option: value,
                    value: fieldValue
                });

            } else {
                storeData[key] = value;
            }

        });

        this.formIsValid = this.form.isValid();
        this.storeData = storeData;
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
            form.add(new Ext.form.TextField({
                fieldLabel: t('form_builder_field_display_name'),
                name: 'display_name',
                value: this.storeData.display_name ? this.storeData.display_name : this.typeName,
                allowBlank: false,
                anchor: '100%',
                enableKeyEvents: true
            }));

            //create "name" field.
            form.add(new Ext.form.TextField({
                fieldLabel:  t('form_builder_field_name'),
                name: 'name',
                value: this.storeData.name ? this.storeData.name : this.generateUniqueFieldName(),
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
            form.add(new Ext.form.ComboBox({
                fieldLabel: t('form_builder_field_template'),
                name: 'template',
                value: this.storeData.template ? this.storeData.template : templateDefaultValue,
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
                    value: this.storeData[fieldConfig.id],
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
                    value: this.storeData[fieldConfig.id],
                });

                break;

            case 'checkbox':

                field = new Ext.form.Checkbox({

                    fieldLabel: fieldConfig.label,
                    name: fieldConfig.id,
                    checked: false,
                    uncheckedValue: false,
                    inputValue: true,
                    value: this.storeData[fieldConfig.id]
                });

                break;

            case 'textfield':

                field = new Ext.form.TextField({

                    fieldLabel: fieldConfig.label,
                    name: fieldConfig.id,
                    value: this.storeData[fieldConfig.id],
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
                    value: this.storeData[fieldConfig.id],
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

        var keyValueRepeater = null,
            metaDataCounter = 0,
            allowFirstOptionsEmpty = false;

        var addMetaData = function (name, value) {

            if(typeof name !== 'string') {
                name = '';
            }
            if(typeof value !== 'string') {
                value = '';
            }

            var count = keyValueRepeater.query('button').length;

            var compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: 'padding-bottom:5px;',
                items: [
                    {
                        xtype: 'textfield',
                        name: 'repeater__' + fieldConfig.id + '__' + count + '__option',
                        fieldLabel: t('form_builder_option'),
                        anchor: '100%',
                        summaryDisplay: true,
                        allowBlank: allowFirstOptionsEmpty === true && metaDataCounter === 0,
                        value : name,
                        flex: 1,
                        margin: '0 10px 0 0'
                    },
                    {
                        xtype: 'textfield',
                        name: 'repeater__' + fieldConfig.id + '__' + count + '__value',
                        fieldLabel: t('form_builder_value'),
                        anchor: '100%',
                        summaryDisplay: true,
                        allowBlank: false,
                        value : value,
                        flex: 1,
                        margin: '0 10px 0 0'
                    }
                ]
            });

            compositeField.add([{
                xtype: 'button',
                iconCls: 'pimcore_icon_delete',
                style: 'float:left;',
                handler: function (compositeField, el) {
                    keyValueRepeater.remove(compositeField);
                    keyValueRepeater.updateLayout();
                }.bind(this, compositeField)
            },{
                xtype: 'box',
                style: 'clear:both;'
            }]);

            keyValueRepeater.add(compositeField);
            keyValueRepeater.updateLayout();

            metaDataCounter++;

        }.bind(this);

        var items = [
            '->',
            {
                xtype: 'button',
                text: t('add'),
                iconCls: 'pimcore_icon_add',
                handler: addMetaData,
                tooltip: {
                    title:'',
                    text: t('form_builder_add_metadata')
                }
            }
        ];

        if( allowFirstOptionsEmpty ) {
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

        keyValueRepeater = new Ext.form.FieldSet({

            title: fieldConfig.label,
            collapsible: false,
            autoHeight:true,
            width: '100%',
            style: 'margin-top: 20px;',
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px;',
                items: items
            }]
        });

        try {

            if(typeof this.storeData[fieldConfig.id] === 'object' && this.storeData[fieldConfig.id].length > 0) {
                this.storeData[fieldConfig.id].forEach(function(field) {
                    addMetaData(field['option'], field['value']);
                });
            }

        } catch (e) {}


        return keyValueRepeater;

    },

    getRepeaterWithOptions: function(fieldConfig) {

        var optionsRepeater = null,
            metaDataCounter = 0,
            optionsStore = new Ext.data.ArrayStore({
            fields: ['label','value'],
            data : fieldConfig.config.options
        });

        var addMetaData = function (name, value) {

            if(typeof name !== 'string') {
                name = '';
            }
            if(typeof value !== 'string') {
                value = '';
            }

            var count = optionsRepeater.query('button').length;

            var compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: 'padding-bottom:5px;',
                items: [
                    {
                        xtype: 'combo',
                        name: 'repeater__' + fieldConfig.id + '__' + count + '__option',
                        fieldLabel: t('form_builder_option'),
                        queryDelay: 0,
                        displayField: 'label',
                        valueField: 'value',
                        mode: 'local',
                        store: optionsStore,
                        editable: true,
                        triggerAction: 'all',
                        anchor: "100%",
                        value: name,
                        summaryDisplay: true,
                        allowBlank: false,
                        flex: 1,
                        margin: '0 10px 0 0'
                    },
                    {
                        xtype: 'textfield',
                        name: 'repeater__' + fieldConfig.id + '__' + count + '__value',
                        fieldLabel: t('form_builder_value'),
                        anchor: '100%',
                        value: value,
                        summaryDisplay: true,
                        allowBlank: false,
                        flex: 1,
                        margin: '0 10px 0 0'
                    }
                ]

            });

            compositeField.add([{
                xtype: 'button',
                iconCls: 'pimcore_icon_delete',
                style: 'float:left;',
                handler: function (compositeField, el) {
                    optionsRepeater.remove(compositeField);
                    optionsRepeater.updateLayout();
                }.bind(this, compositeField)
            },{
                xtype: 'box',
                style: 'clear:both;'
            }]);

            optionsRepeater.add(compositeField);
            optionsRepeater.updateLayout();

            metaDataCounter++;

        }.bind(this);

        optionsRepeater = new Ext.form.FieldSet({

            title: fieldConfig.label,
            collapsible: false,
            autoHeight:true,
            width: '100%',
            style: 'margin-top: 20px;',
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px;',
                items: ['->', {
                    xtype: 'button',
                    text: t('add'),
                    iconCls: 'pimcore_icon_add',
                    handler: addMetaData,
                    tooltip: {
                        title:'',
                        text: t('form_builder_add_metadata')
                    }
                }]
            }]
        });

        try {

            if(typeof this.storeData[fieldConfig.id] === 'object' && this.storeData[fieldConfig.id].length > 0) {
                this.storeData[fieldConfig.id].forEach(function(field) {
                    addMetaData(field['option'], field['value'] );
                });
            }

        } catch (e) {}

        return optionsRepeater;
    },

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
            return in_array(component.name, ['label']);
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

            this.markInvalid( t("Path doesn't exist") );

        }

    }

});