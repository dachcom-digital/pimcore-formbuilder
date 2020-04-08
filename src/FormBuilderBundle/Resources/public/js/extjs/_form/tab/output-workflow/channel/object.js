pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.object');
Formbuilder.extjs.formPanel.outputWorkflow.channel.object = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel, {

    objectResolverPanel: null,
    objectEditorControlPanel: null,

    objectMappingData: null,
    objectMappingDataIsConsistent: true,
    lastConsistentConfigHash: null,

    panel: null,

    getLayout: function () {

        var formConfig;

        this.objectResolverPanel = null;
        this.objectMappingDataIsConsistent = true;
        this.objectMappingData = this.data !== null && this.data.hasOwnProperty('objectMappingData') ? this.data['objectMappingData'] : null;

        if (this.data !== null) {
            formConfig = this.data;
            if (formConfig.hasOwnProperty('objectMappingData')) {
                delete formConfig['objectMappingData'];
                if (formConfig.hasOwnProperty('dynamicObjectResolver') && formConfig.dynamicObjectResolver === null) {
                    formConfig.dynamicObjectResolver = '';
                }
                this.lastConsistentConfigHash = md5(Ext.encode(formConfig));
            }
        }

        this.panel = new Ext.form.FormPanel({
            title: false,
            border: false,
            defaults: {},
            items: this.getConfigFields()
        });

        this.panel.on('afterrender', function () {
            var objectResolverValue = this.data !== null && this.data.hasOwnProperty('resolveStrategy') ? this.data['resolveStrategy'] : 'newObject';
            this.generateObjectResolverPanel(objectResolverValue);
        }.bind(this));

        return this.panel;
    },

    getConfigFields: function () {

        var objectResolverValue = this.data !== null && this.data.hasOwnProperty('resolveStrategy') ? this.data['resolveStrategy'] : 'newObject';

        return [
            {
                xtype: 'combo',
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.resolve_strategy'),
                queryDelay: 0,
                displayField: 'key',
                valueField: 'value',
                mode: 'local',
                labelAlign: 'left',
                store: new Ext.data.ArrayStore({
                    fields: ['value', 'key'],
                    data: [
                        ['newObject', t('form_builder.output_workflow.output_workflow_channel.object.resolve_with_new_object')],
                        ['existingObject', t('form_builder.output_workflow.output_workflow_channel.object.resolve_with_existing_object')],
                    ]
                }),
                value: objectResolverValue,
                editable: false,
                triggerAction: 'all',
                anchor: '100%',
                summaryDisplay: true,
                allowBlank: false,
                name: 'resolveStrategy',
                listeners: {
                    change: function (field, value) {
                        this.generateObjectResolverPanel(value);
                    }.bind(this)
                }
            }
        ]
    },

    generateObjectResolverPanel: function (value) {

        this.isLoading = true;

        if (this.objectResolverPanel !== null) {
            this.panel.remove(this.objectResolverPanel);
        }

        this.objectResolverPanel = new Ext.form.FieldSet({
            title: value === 'newObject'
                ? t('form_builder.output_workflow.output_workflow_channel.object.resolve_with_new_object')
                : t('form_builder.output_workflow.output_workflow_channel.object.resolve_with_existing_object'),
            collapsible: false,
            collapsed: false,
            autoHeight: true,
            defaults: {
                labelWidth: 200
            },
            defaultType: 'textfield'
        });

        if (value === 'newObject') {
            this.objectResolverPanel.add(this.generateNewObjectResolverPanel());
        } else if (value === 'existingObject') {
            this.objectResolverPanel.add(this.generateExistingObjectResolverPanel());
        } else {
            // throw error.
        }

        this.objectResolverPanel.add(this.generateObjectEditorControlPanel());

        this.panel.add(this.objectResolverPanel);

    },

    generateNewObjectResolverPanel: function () {

        var firstTimeLoad = true,
            storagePathValue = this.data !== null && this.data.hasOwnProperty('storagePath') ? this.data['storagePath'] : null,
            storagePathFieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.object.storage_path'),
                id: 'storagePath',
                config: {
                    types: ['object'],
                    subtypes: {object: ['folder']}
                }
            },
            comboBox = new Ext.form.ComboBox({
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.choose_resolving_object_class'),
                displayField: 'label',
                valueField: 'key',
                mode: 'local',
                queryMode: 'local',
                labelAlign: 'left',
                value: null,
                triggerAction: 'all',
                anchor: '100%',
                editable: false,
                summaryDisplay: true,
                allowBlank: false,
                name: 'resolvingObjectClass',
                listeners: {
                    change: function (field, value) {
                        if (firstTimeLoad === true) {
                            firstTimeLoad = false;
                            return;
                        }
                        this.validateResolverStrategy();
                    }.bind(this),
                    render: function (combo) {
                        combo.getStore().load();
                    }.bind(this),
                }
            }), store = new Ext.data.Store({
                autoLoad: false,
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/formbuilder/output-workflow/object/get-object-classes',
                    fields: ['label', 'key'],
                    reader: {
                        type: 'json',
                        rootProperty: 'types'
                    },
                },
                listeners: {
                    load: function (tree, records, success, opt) {
                        comboBox.setValue(this.data !== null && this.data.hasOwnProperty('resolvingObjectClass') ? this.data['resolvingObjectClass'] : '');
                        this.isLoading = false;
                        this.validateResolverStrategy();
                        firstTimeLoad = false;
                    }.bind(this)
                }
            }), storagePathHrefField, storagePathHref;

        comboBox.setStore(store);

        storagePathHrefField = new Formbuilder.extjs.types.href(storagePathFieldConfig, storagePathValue, null);
        storagePathHref = storagePathHrefField.getHref();
        storagePathHref.allowBlank = false;

        storagePathHref.on({
            afterrender: function () {
                this.validateResolverStrategy();
            }.bind(this),
            change: function () {
                this.validateResolverStrategy();
            }.bind(this)
        });

        return [
            storagePathHref,
            comboBox
        ];
    },

    generateExistingObjectResolverPanel: function () {

        var firstTimeLoad = true,
            resolvingObjectValue = this.data !== null && this.data.hasOwnProperty('resolvingObject') ? this.data['resolvingObject'] : null,
            dynamicObjectResolverValue = this.data !== null && this.data.hasOwnProperty('dynamicObjectResolver') ? this.data['dynamicObjectResolver'] : null,
            resolvingObjectFieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.object.choose_resolving_object'),
                id: 'resolvingObject',
                config: {
                    types: ['object'],
                    subtypes: {object: ['object']}
                }
            },
            resolvingObjectHrefField,
            resolvingObjectHref,
            dynamicObjectResolverCombo,
            dynamicObjectResolverStore;

        resolvingObjectHrefField = new Formbuilder.extjs.types.href(resolvingObjectFieldConfig, resolvingObjectValue, null);
        resolvingObjectHref = resolvingObjectHrefField.getHref();
        resolvingObjectHref.allowBlank = false;
        resolvingObjectHref.on({
            afterrender: function () {
                this.validateResolverStrategy();
            }.bind(this),
            change: function () {
                this.validateResolverStrategy();
            }.bind(this)
        });

        dynamicObjectResolverCombo = new Ext.form.ComboBox({
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.dynamic_object_resolver'),
            name: 'dynamicObjectResolver',
            value: null,
            displayField: 'label',
            valueField: 'key',
            mode: 'local',
            queryMode: 'local',
            labelAlign: 'left',
            triggerAction: 'all',
            editable: false,
            summaryDisplay: true,
            emptyText: t('form_builder.output_workflow.output_workflow_channel.object.dynamic_object_no_resolver'),
            allowBlank: true,
            disabled: true,
            listeners: {
                change: function (field, value) {
                    if (firstTimeLoad === true) {
                        firstTimeLoad = false;
                        return;
                    }
                    this.validateResolverStrategy();
                }.bind(this),
                render: function (combo) {
                    combo.getStore().load();
                }.bind(this),
            }
        });

        dynamicObjectResolverStore = new Ext.data.Store({
            autoLoad: false,
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: '/admin/formbuilder/output-workflow/object/get-dynamic-object-resolver',
                fields: ['label', 'key'],
                reader: {
                    type: 'json',
                    rootProperty: 'resolver'
                }
            },
            listeners: {
                load: function (store, records) {

                    store.insert(0, new Ext.data.Record({
                        key: null,
                        label: t('form_builder.output_workflow.output_workflow_channel.object.dynamic_object_no_resolver')
                    }));

                    if (records.length > 0) {
                        dynamicObjectResolverCombo.setDisabled(false);
                    }

                    dynamicObjectResolverCombo.setValue(dynamicObjectResolverValue);
                    this.isLoading = false;
                    this.validateResolverStrategy();
                    firstTimeLoad = false;

                }.bind(this)
            }
        });

        dynamicObjectResolverCombo.setStore(dynamicObjectResolverStore);

        return [
            resolvingObjectHref,
            dynamicObjectResolverCombo,
        ];
    },

    validateResolverStrategy: function () {

        var isValid = this.panel.form.isValid(),
            controlPanel = this.panel.query('panel[cls~="form_builder_channel_object_editor_control_panel"]'),
            currentConfigHash = md5(Ext.encode(this.panel.form.getValues()));

        if (controlPanel.length === 0) {
            return;
        }

        if (this.objectMappingData !== null) {
            this.objectMappingDataIsConsistent = currentConfigHash === this.lastConsistentConfigHash
        } else {
            this.objectMappingDataIsConsistent = true;
        }

        controlPanel[0].setVisible(isValid);

        this.checkObjectMappingEditorSignals();
    },

    generateObjectEditorControlPanel: function () {

        var hasData = this.objectMappingData !== null,
            hasInconsistentData = this.objectMappingDataIsConsistent === false;

        return new Ext.Panel({
            layout: 'hbox',
            anchor: '100%',
            hidden: true,
            cls: 'form_builder_channel_object_editor_control_panel',
            width: 350,
            hideLabel: true,
            autoHeight: true,
            items: [
                {
                    xtype: 'button',
                    iconCls: 'form_builder_output_workflow_channel_object_mapper',
                    text: t('form_builder.output_workflow.output_workflow_channel.object.start_object_mapping'),
                    style: 'background: #404f56; border-color: transparent;',
                    handler: this.showObjectMappingEditor.bind(this)
                },
                {
                    xtype: 'button',
                    cls: 'form_builder_cme_status_button',
                    style: 'background: ' + (hasData ? (hasInconsistentData ? '#d64517' : '#3e943e') : '#7f8a7f') + '; border-color: transparent; cursor: default !important;',
                    text: t('form_builder.output_workflow.output_workflow_channel.object.object_mapping.' + (hasData ? (hasInconsistentData ? 'status_inconsistent' : 'status_in_sync') : 'status_disabled'))
                }
            ]
        });
    },

    showObjectMappingEditor: function () {

        var setupConfiguration = this.getValues(),
            addParams = {channelId: this.channelId},
            callbacks = {
                loadData: function () {
                    if (this.objectMappingData !== null) {
                        return this.objectMappingData;
                    }

                    return null;

                }.bind(this),
                saveData: function (data) {
                    this.objectMappingData = data;
                    this.lastConsistentConfigHash = md5(Ext.encode(this.panel.form.getValues()));
                    this.objectMappingDataIsConsistent = true;
                    this.checkObjectMappingEditorSignals();

                    Formbuilder.eventObserver.getObserver(this.formId).fireEvent('output_workflow.required_form_fields_refreshed', {workflowId: this.workflowId});

                }.bind(this)
            };

        new Formbuilder.extjs.extensions.formObjectMappingEditor(this.formId, addParams, setupConfiguration, true, callbacks);
    },

    checkObjectMappingEditorSignals: function () {

        if (this.isLoading === true) {
            return;
        }

        var hasData = this.objectMappingData !== null,
            hasInconsistentData = this.objectMappingDataIsConsistent === false,
            statusButtons = this.panel.query('panel[cls~="form_builder_channel_object_editor_control_panel"] button[cls="form_builder_cme_status_button"]');

        if (statusButtons.length > 0) {
            setTimeout(function () {
                statusButtons[0].setStyle('background', hasData ? (hasInconsistentData ? '#d64517' : '#3e943e') : '#7f8a7f');
                statusButtons[0].setText(t('form_builder.output_workflow.output_workflow_channel.object.object_mapping.' + (hasData ? (hasInconsistentData ? 'status_inconsistent' : 'status_in_sync') : 'status_disabled')));
            }, 150)
        }
    },

    isValid: function () {
        var hasValidObjectMappingData = this.objectMappingData !== null && this.objectMappingDataIsConsistent === true;
        return hasValidObjectMappingData && this.panel.form.isValid();
    },

    getValues: function () {

        var formValues = this.panel.form.getValues();
        formValues['objectMappingData'] = this.objectMappingData;

        return formValues;
    },

    getUsedFormFields: function () {

        if (this.objectMappingData === null) {
            return [];
        }

        return this.getRequiredFormFieldNamesRecursive(this.objectMappingData, []);
    },

    getRequiredFormFieldNamesRecursive: function (fields, fieldNames) {

        Ext.Array.each(fields, function (node) {

            var hasChilds = node.hasOwnProperty('childs') && Ext.isArray(node.childs) && node.childs.length > 0,
                hasWorkerFieldMapping = node.config.hasOwnProperty('workerData') && node.config.workerData.hasOwnProperty('fieldMapping');

            if (node.hasOwnProperty('type')
                && node.type === 'form_field'
                && hasChilds === true) {
                fieldNames.push(node.config.name)
            }

            if (hasChilds === true) {
                this.getRequiredFormFieldNamesRecursive(node.childs, fieldNames);
            } else if (hasWorkerFieldMapping === true) {
                this.getRequiredFormFieldNamesRecursive(node.config.workerData.fieldMapping, fieldNames);
            }

        }.bind(this));

        return fieldNames;
    }
});