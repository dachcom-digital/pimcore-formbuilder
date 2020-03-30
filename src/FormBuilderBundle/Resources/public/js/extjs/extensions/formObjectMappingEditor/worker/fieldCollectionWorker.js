pimcore.registerNS('Formbuilder.extjs.extensions.formObjectMappingEditorWorker.fieldCollectionWorker');
Formbuilder.extjs.extensions.formObjectMappingEditorWorker.fieldCollectionWorker = Class.create({

    formId: null,
    classId: null,
    fieldCollectionKey: null,
    data: null,

    window: null,
    editPanel: null,
    comboBox: null,
    node: null,

    hasValidData: false,
    fieldCollectionValidator: null,
    formObjectTreeMapper: null,

    initialize: function (formId, classId, fieldCollectionKey, data) {

        this.formId = formId;
        this.classId = classId;
        this.fieldCollectionKey = fieldCollectionKey;
        this.data = data;

        this.hasValidData = false;

        if (data !== null) {
            if (data.hasOwnProperty('fieldCollectionClassKey')) {
                if (data.hasOwnProperty('fieldMapping') && Ext.isArray(data.fieldMapping) && data.fieldMapping.length > 0) {
                    this.hasValidData = true;
                }
            }
        }
    },

    getName: function () {
        return 'fieldCollectionWorker';
    },

    getConfigDialog: function (node, cb) {

        this.node = node;

        var firstTimeLoad = true,
            comboBox = new Ext.form.ComboBox({
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_fieldcollection'),
                displayField: 'label',
                valueField: 'key',
                mode: 'local',
                queryMode: 'local',
                labelAlign: 'left',
                value: null,
                triggerAction: 'all',
                anchor: '100%',
                labelWidth: 180,
                editable: false,
                summaryDisplay: true,
                allowBlank: false,
                name: 'fieldCollection',
                listeners: {
                    change: function (field, value) {
                        if (firstTimeLoad === true) {
                            firstTimeLoad = false;
                            return;
                        }
                        this.changeFieldCollection(value, true);
                    }.bind(this),
                    render: function (combo) {
                        combo.getStore().load();
                    }.bind(this),
                }
            }),
            store = new Ext.data.Store({
                autoLoad: false,
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/formbuilder/output-workflow/object/get-field-collection-types',
                    fields: ['label', 'key'],
                    extraParams: {
                        classId: this.classId,
                        fieldCollectionKey: this.fieldCollectionKey
                    },
                    reader: {
                        type: 'json',
                        rootProperty: 'types'
                    },
                },
                listeners: {
                    load: function (store, records) {

                        var initValue = null;

                        if (this.data !== null && this.data.hasOwnProperty('fieldCollectionClassKey')) {
                            initValue = this.data.fieldCollectionClassKey;
                        } else if (records.length === 1) {
                            initValue = records[0].get('key');
                        }

                        if (initValue !== null) {
                            comboBox.setValue(initValue);
                            this.changeFieldCollection(initValue);
                        }
                        firstTimeLoad = false;
                    }.bind(this)
                }
            });

        comboBox.setStore(store);

        this.comboBox = comboBox;

        this.window = new Ext.Window({
            width: 900,
            height: 540,
            iconCls: 'form_builder_output_workflow_channel_object_mapper',
            layout: 'fit',
            closeAction: 'destroy',
            plain: true,
            autoScroll: true,
            autoHeight: true,
            preventRefocus: true,
            cls: 'formbuilder-object-mapping-editor-fieldcollection',
            title: 'Field Collection Configurator',
            modal: true,
            items: [this.configPanel],
            buttons: [
                {
                    text: t('form_builder.output_workflow.apply_and_close'),
                    iconCls: 'form_builder_output_workflow_apply_data',
                    handler: this.commitData.bind(this, cb)
                }
            ]
        });

        this.window.addDocked({
            xtype: 'toolbar',
            dock: 'top',
            items: [this.comboBox]
        });

        this.window.show();

        return this.window;
    },

    changeFieldCollection: function (fieldCollectionKey, switchLayout) {

        var pimcoreClassType = 'fieldcollection',
            parentNode = this.node.parentNode,
            containerFields = parentNode.data.omContainerFields,
            values = this.data && this.data.hasOwnProperty('fieldMapping') ? this.data.fieldMapping : null,
            formObjectTreeMapperPanel;

        if (this.editPanel !== null) {
            this.window.remove(this.editPanel);
        }

        if (switchLayout === true) {
            values = null;
        }

        this.fieldCollectionValidator = this.generateFieldCollectionValidator(fieldCollectionKey);
        this.formObjectTreeMapper = new Formbuilder.extjs.extensions.formObjectMappingEditorConfigurator.formObjectTreeMapper(
            this.formId,
            values,
            containerFields,
            pimcoreClassType,
            fieldCollectionKey,
            parentNode.data.text,
            parentNode.data.iconCls
        );

        formObjectTreeMapperPanel = this.formObjectTreeMapper.getLayout();
        formObjectTreeMapperPanel.region = 'center';

        this.editPanel = new Ext.form.Panel({
            layout: 'border',
            items: [
                formObjectTreeMapperPanel,
                this.fieldCollectionValidator
            ]
        });

        this.window.add(this.editPanel);
    },

    generateFieldCollectionValidator: function (fieldCollectionKey) {

        var _ = this;

        return new Ext.form.FormPanel({
            title: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_validation_configuration'),
            collapsible: true,
            collapsed: true,
            titleCollapse: false,
            border: false,
            floatable: false,
            hideCollapseTool: false,
            region: 'south',
            style: 'padding: 10px;',
            items: [
                {
                    xtype: 'fieldcontainer',
                    layout: 'hbox',
                    style: 'margin: 5px 0 0 0;',
                    items: [
                        {
                            xtype: 'hidden',
                            name: 'validation.count.type',
                            value: 'count'
                        },
                        {
                            xtype: 'checkbox',
                            name: 'validation.count.enabled',
                            checked: this.findValueInValidationData('count', 'enabled', false),
                            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_enable_count_validation'),
                            labelAlign: 'left',
                            inputValue: true,
                            uncheckedValue: false,
                            flex: 1,
                            listeners: {
                                change: function (cb, val) {
                                    var fc = this.up('fieldcontainer');
                                    fc.down('combo[name="validation.count.field"]').setDisabled(!val);
                                    fc.down('textfield[name="validation.count.message"]').setDisabled(!val);
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            name: 'validation.count.field',
                            value: null,
                            disabled: this.findValueInValidationData('count', 'enabled', false) === false,
                            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_referencing_count_field'),
                            displayField: 'label',
                            valueField: 'key',
                            labelAlign: 'left',
                            queryMode: 'local',
                            triggerAction: 'all',
                            editable: false,
                            allowBlank: true,
                            style: 'margin: 0 10px 0 0',
                            flex: 2,
                            listeners: {
                                afterrender: function (cb) {
                                    cb.store.load({
                                        callback: function (records) {
                                            cb.setValue(_.findValueInValidationData('count', 'field', null));
                                        }
                                    });
                                }
                            },
                            store: new Ext.data.Store({
                                autoLoad: false,
                                proxy: {
                                    type: 'ajax',
                                    url: '/admin/formbuilder/output-workflow/object/get-object-class-fields',
                                    extraParams: {
                                        type: 'dataClass',
                                        id: this.classId
                                    },
                                    fields: ['label', 'key'],
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'fields'
                                    }
                                }
                            })
                        },
                        {
                            xtype: 'textfield',
                            name: 'validation.count.message',
                            value: this.findValueInValidationData('count', 'message', null),
                            disabled: this.findValueInValidationData('count', 'enabled', false) === false,
                            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_validation_message'),
                            emptyText: t('form_builder_success_message_text_empty'),
                            labelAlign: 'left',
                            summaryDisplay: true,
                            allowBlank: true,
                            flex: 3
                        }
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    layout: 'hbox',
                    items: [
                        {
                            xtype: 'hidden',
                            name: 'validation.unique.type',
                            value: 'unique'
                        },
                        {
                            xtype: 'checkbox',
                            name: 'validation.unique.enabled',
                            checked: this.findValueInValidationData('unique', 'enabled', false),
                            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_enable_uniquenes_validation'),
                            labelAlign: 'left',
                            inputValue: true,
                            uncheckedValue: false,
                            flex: 1,
                            listeners: {
                                change: function (cb, val) {
                                    var fc = this.up('fieldcontainer');
                                    fc.down('combo[name="validation.unique.field"]').setDisabled(!val);
                                    fc.down('textfield[name="validation.unique.message"]').setDisabled(!val);
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            name: 'validation.unique.field',
                            value: null,
                            disabled: this.findValueInValidationData('unique', 'enabled', false) === false,
                            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_referencing_unique_field'),
                            displayField: 'label',
                            valueField: 'key',
                            labelAlign: 'left',
                            queryMode: 'local',
                            triggerAction: 'all',
                            editable: false,
                            allowBlank: true,
                            style: 'margin: 0 10px 0 0',
                            flex: 2,
                            listeners: {
                                afterrender: function (cb) {
                                    this.store.load({
                                        callback: function (records) {
                                            cb.setValue(_.findValueInValidationData('unique', 'field', null));
                                        }
                                    });
                                }
                            },
                            store: new Ext.data.Store({
                                autoLoad: false,
                                proxy: {
                                    type: 'ajax',
                                    url: '/admin/formbuilder/output-workflow/object/get-object-class-fields',
                                    extraParams: {
                                        type: 'fieldCollection',
                                        id: fieldCollectionKey
                                    },
                                    fields: ['label', 'key'],
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'fields'
                                    },
                                }
                            })
                        },
                        {
                            xtype: 'textfield',
                            name: 'validation.unique.message',
                            value: this.findValueInValidationData('unique', 'message', null),
                            disabled: this.findValueInValidationData('unique', 'enabled', false) === false,
                            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_validation_message'),
                            emptyText: t('form_builder_success_message_text_empty'),
                            labelAlign: 'left',
                            summaryDisplay: true,
                            allowBlank: true,
                            flex: 3
                        }
                    ]
                }
            ]
        });
    },

    isReadyToConfigure: function (node) {
        this.node = node;

        if (!this.node.parentNode) {
            return false;
        }

        return this.node.parentNode.data.omFieldTypeIdentifier === 'form_field';
    },

    findValueInValidationData: function (section, key, defaultValue) {

        var value = defaultValue;

        if (this.data === null || !this.data.hasOwnProperty('validationData')) {
            return defaultValue;
        }

        Ext.Object.each(this.data.validationData, function (index, data) {
            if (data.type === section) {
                value = data.hasOwnProperty(key) ? data[key] : defaultValue;
                return false;
            }
        });

        return value;
    },

    isValid: function (node) {
        return this.hasValidData === true;
    },

    getData: function () {
        return this.data;
    },

    commitData: function (cb) {

        var validationData,
            transposedValidationData,
            validationDataArray = [];

        if (this.formObjectTreeMapper === null) {
            return;
        }

        this.hasValidData = this.formObjectTreeMapper.isValid();

        transposedValidationData = DataObjectParser.transpose(this.fieldCollectionValidator.form.getValues());
        validationData = transposedValidationData.data();

        if (validationData.hasOwnProperty('validation')) {
            Ext.Object.each(validationData['validation'], function (index, data) {
                validationDataArray.push(data);
            });
        }

        this.data = {
            'fieldCollectionClassKey': this.comboBox.getValue(),
            'fieldMapping': this.formObjectTreeMapper.getEditorData(),
            'validationData': validationDataArray
        };

        this.window.close();

        if (typeof cb === 'function') {
            cb();
        }
    }
});