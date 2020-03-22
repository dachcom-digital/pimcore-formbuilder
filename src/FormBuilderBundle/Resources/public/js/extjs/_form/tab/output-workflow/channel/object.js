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

        var storagePathValue = this.data !== null && this.data.hasOwnProperty('storagePath') ? this.data['storagePath'] : null,
            objectResolverValue = this.data !== null && this.data.hasOwnProperty('resolveStrategy') ? this.data['resolveStrategy'] : 'newObject',
            fieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.object.storage_path'),
                id: 'storagePath',
                config: {
                    types: ['object'],
                    subtypes: {object: ['folder']}
                }
            }, storagePathHrefField, storagePathHref;

        storagePathHrefField = new Formbuilder.extjs.types.href(fieldConfig, storagePathValue, null);

        storagePathHref = storagePathHrefField.getHref();
        storagePathHref.allowBlank = false;

        storagePathHref.on('afterrender', function () {
            this.validateResolverStrategy();
        }.bind(this));

        storagePathHref.on('change', function () {
            this.validateResolverStrategy();
        }.bind(this));

        return [
            storagePathHref,
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
                        this.validateResolverStrategy();
                        firstTimeLoad = false;
                    }.bind(this)
                }
            });

        comboBox.setStore(store);

        return [comboBox];

    },

    generateExistingObjectResolverPanel: function () {

        var storagePathValue = this.data !== null && this.data.hasOwnProperty('resolvingObject') ? this.data['resolvingObject'] : null,
            fieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.object.choose_resolving_object'),
                id: 'resolvingObject',
                config: {
                    types: ['object'],
                    subtypes: {object: ['object']}
                }
            }, storagePathHrefField, storagePathHref;

        storagePathHrefField = new Formbuilder.extjs.types.href(fieldConfig, storagePathValue, null);

        storagePathHref = storagePathHrefField.getHref();
        storagePathHref.allowBlank = false;

        storagePathHref.on('afterrender', function () {
            this.validateResolverStrategy();
        }.bind(this));

        storagePathHref.on('change', function () {
            this.validateResolverStrategy();
        }.bind(this));

        return [
            storagePathHref,
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
                    disable: true,
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
                }.bind(this)
            };

        new Formbuilder.extjs.extensions.formObjectMappingEditor(this.formId, addParams, setupConfiguration, true, callbacks);
    },

    checkObjectMappingEditorSignals: function () {

        var hasData = this.objectMappingData !== null,
            hasInconsistentData = this.objectMappingDataIsConsistent === false,
            statusButtons = this.panel.query('panel[cls~="form_builder_channel_object_editor_control_panel"] button[cls="form_builder_cme_status_button"]');

        if (statusButtons.length > 0) {
            statusButtons[0].setStyle('background', hasData ? (hasInconsistentData ? '#d64517' : '#3e943e') : '#7f8a7f');
            statusButtons[0].setText(t('form_builder.output_workflow.output_workflow_channel.object.object_mapping.' + (hasData ? (hasInconsistentData ? 'status_inconsistent' : 'status_in_sync') : 'status_disabled')));
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
    }
});