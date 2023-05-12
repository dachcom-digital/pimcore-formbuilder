pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.object');
Formbuilder.extjs.formPanel.outputWorkflow.channel.object = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel, {

    objectResolverPanel: null,
    objectEditorControlPanel: null,

    objectMappingData: null,
    objectMappingDataIsConsistent: true,
    lastConsistentConfigHash: null,

    panel: null,

    getLayout: function () {

        var formConfig,
            objectResolverValue = this.data !== null && this.data.hasOwnProperty('resolveStrategy') ? this.data['resolveStrategy'] : 'newObject';

        this.objectResolverPanel = null;
        this.objectMappingDataIsConsistent = true;
        this.objectMappingData = this.data !== null && this.data.hasOwnProperty('objectMappingData') ? this.data['objectMappingData'] : null;

        if (this.data !== null) {
            formConfig = this.data;
            if (formConfig.hasOwnProperty('objectMappingData')) {
                delete formConfig['objectMappingData'];
                this.lastConsistentConfigHash = md5(Ext.encode(formConfig));
            }
        }

        this.panel = new Ext.form.FormPanel({
            title: false,
            border: false,
            defaults: {},
            items: this.getConfigFields(objectResolverValue)
        });

        this.panel.on('afterrender', function () {
            this.generateObjectResolverPanel(objectResolverValue);
        }.bind(this));

        return this.panel;
    },

    getConfigFields: function (objectResolverValue) {

        return [{
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
                    this.resetObjectMappingData();
                    this.generateObjectResolverPanel(value);
                }.bind(this)
            }
        }]
    },

    generateObjectResolverPanel: function (resolveStrategy) {

        var dynamicObjectResolverValue = this.data !== null && this.data.hasOwnProperty('dynamicObjectResolver') ? this.data['dynamicObjectResolver'] : null,
            dynamicObjectResolverPanel;

        this.isLoading = true;

        if (this.objectResolverPanel !== null) {
            this.panel.remove(this.objectResolverPanel);
        }

        this.objectResolverPanel = new Ext.form.FieldSet({
            title: resolveStrategy === 'newObject'
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

        dynamicObjectResolverPanel = new Ext.Panel({
            defaults: {
                labelWidth: 200
            },
            items: []
        });

        this.objectResolverPanel.add([
            {
                xtype: 'checkbox',
                name: 'useDynamicObjectResolver',
                itemCls: 'dynamicObjectResolverDispatcher',
                submitValue: false,
                hidden: true,
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.use_dynamic_object_resolver'),
                checked: dynamicObjectResolverValue !== null,
                listeners: {
                    change: function (cb, value) {

                        if (value === false) {

                            if (this.data === null) {
                                this.data = {};
                            }

                            this.resetDynamicObjectResolverData();
                            this.resetObjectMappingData();
                        }

                        this.generateObjectOptionsPanel(dynamicObjectResolverPanel, resolveStrategy, value === true);

                    }.bind(this)
                }
            },
            dynamicObjectResolverPanel,
            this.generateObjectEditorControlPanel()
        ]);

        this.assertDynamicObjectResolverList(function (resolverList) {

            var validList = Ext.Array.filter(resolverList, function (record) {
                return Ext.Array.contains(record['allowedObjectResolverModes'], resolveStrategy);
            });

            if (validList.length > 0) {
                this.objectResolverPanel.query('checkbox[itemCls="dynamicObjectResolverDispatcher"]')[0].setHidden(false);
            } else {
                this.resetDynamicObjectResolverData();
            }

            this.generateObjectOptionsPanel(dynamicObjectResolverPanel, resolveStrategy, false);

        }.bind(this));

        this.panel.add(this.objectResolverPanel);
    },

    generateObjectOptionsPanel: function (panel, resolveStrategy, forceDynamicResolver) {

        var dynamicObjectResolverValue = this.data !== null && this.data.hasOwnProperty('dynamicObjectResolver') ? this.data['dynamicObjectResolver'] : null,
            dynamicObjectResolverClassValue = this.data !== null && this.data.hasOwnProperty('dynamicObjectResolverClass') ? this.data['dynamicObjectResolverClass'] : null;

        panel.removeAll();

        if (forceDynamicResolver === true || dynamicObjectResolverValue !== null) {

            panel.add([
                this.generateDynamicObjectResolverListCombo('dynamicObjectResolver', dynamicObjectResolverValue, resolveStrategy),
                this.generateObjectClassListCombo('dynamicObjectResolverClass', dynamicObjectResolverClassValue)
            ]);

            return;
        }

        panel.add(resolveStrategy === 'newObject' ? this.generateNewObjectResolverPanel() : this.generateExistingObjectResolverPanel());
    },

    generateNewObjectResolverPanel: function () {

        var storagePathValue = this.data !== null && this.data.hasOwnProperty('storagePath') ? this.data['storagePath'] : null,
            resolvingObjectClassValue = this.data !== null && this.data.hasOwnProperty('resolvingObjectClass') ? this.data['resolvingObjectClass'] : '',
            storagePathFieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.object.storage_path'),
                id: 'storagePath',
                config: {
                    types: ['object'],
                    subtypes: {object: ['folder']}
                }
            }, storagePathHrefField, storagePathHref;

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
            this.generateObjectClassListCombo('resolvingObjectClass', resolvingObjectClassValue)
        ];
    },

    generateExistingObjectResolverPanel: function () {

        var resolvingObjectValue = this.data !== null && this.data.hasOwnProperty('resolvingObject') ? this.data['resolvingObject'] : null,
            resolvingObjectFieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.object.choose_resolving_object'),
                id: 'resolvingObject',
                config: {
                    types: ['object'],
                    subtypes: {object: ['object']}
                }
            },
            resolvingObjectHrefField,
            resolvingObjectHref;

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

        return [
            resolvingObjectHref
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

    resetObjectMappingData: function () {
        this.objectMappingData = null;
    },

    resetDynamicObjectResolverData: function () {
        this.data.dynamicObjectResolver = null;
        this.data.dynamicObjectResolverClass = null;
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
    },

    generateObjectClassListCombo: function (name, initialValue) {

        var combo,
            firstTimeLoad = true;

        combo = new Ext.form.ComboBox({
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.choose_resolving_object_class'),
            name: name,
            width: 400,
            value: null,
            displayField: 'label',
            valueField: 'key',
            mode: 'local',
            queryMode: 'local',
            labelAlign: 'left',
            triggerAction: 'all',
            anchor: '100%',
            editable: false,
            summaryDisplay: true,
            allowBlank: false,
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
            },
            store: new Ext.data.Store({
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
                    beforeload: function () {
                        this.isLoading = true;
                    }.bind(this),
                    load: function (tree, records, success, opt) {
                        combo.setValue(initialValue);
                        this.isLoading = false;
                        this.validateResolverStrategy();
                        firstTimeLoad = false;
                    }.bind(this)
                }
            })
        });

        return combo;
    },

    generateDynamicObjectResolverListCombo: function (name, initialValue, resolveStrategy) {

        var combo,
            firstTimeLoad = true;

        combo = new Ext.form.ComboBox({
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.dynamic_object_resolver'),
            name: name,
            width: 400,
            value: null,
            displayField: 'label',
            valueField: 'key',
            mode: 'local',
            queryMode: 'local',
            labelAlign: 'left',
            triggerAction: 'all',
            editable: false,
            summaryDisplay: true,
            allowBlank: false,
            emptyText: t('form_builder.output_workflow.output_workflow_channel.object.dynamic_object_no_resolver'),
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
            },
            store: new Ext.data.Store({
                autoLoad: false,
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/formbuilder/output-workflow/object/get-dynamic-object-resolver',
                    fields: ['label', 'key'],
                    extraParams: {
                        allowedObjectResolverMode: resolveStrategy
                    },
                    reader: {
                        type: 'json',
                        rootProperty: 'resolver'
                    }
                },
                listeners: {
                    beforeload: function () {
                        this.isLoading = true;
                    }.bind(this),
                    load: function (store, records) {
                        combo.setValue(initialValue);
                        this.isLoading = false;
                        this.validateResolverStrategy();
                        firstTimeLoad = false;
                    }.bind(this)
                }
            })
        });

        return combo;
    },

    assertDynamicObjectResolverList: function (callback) {
        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/object/get-dynamic-object-resolver',
            success: function (response) {
                var responseData = Ext.decode(response.responseText);
                callback(responseData.resolver);
            }.bind(this)
        });
    }
});