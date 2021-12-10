pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.api');
Formbuilder.extjs.formPanel.outputWorkflow.channel.api = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel, {

    apiProviderPanel: null,
    apiMappingData: null,
    apiConfiguration: null,
    panel: null,
    mappingDataIsConsistent: true,
    lastConsistentConfigHash: null,

    getLayout: function () {

        var formConfig;

        this.mappingDataIsConsistent = true;
        this.apiMappingData = this.data !== null && this.data.hasOwnProperty('apiMappingData') ? this.data['apiMappingData'] : null;
        this.apiConfiguration = this.data !== null && this.data.hasOwnProperty('apiConfiguration') ? this.data['apiConfiguration'] : null;

        this.panel = new Ext.form.FormPanel({
            title: false,
            border: false,
            defaults: {},
            items: this.buildApiProviderSelector()
        });

        if (this.data !== null) {
            formConfig = this.data;
            if (formConfig.hasOwnProperty('apiMappingData')) {
                delete formConfig['apiMappingData'];
                this.lastConsistentConfigHash = md5(Ext.encode(formConfig));
            }
        }

        return this.panel;
    },

    buildApiProviderSelector: function () {

        var comboBox = new Ext.form.ComboBox({
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.api.choose_api_provider'),
                displayField: 'label',
                labelWidth: 150,
                valueField: 'key',
                mode: 'local',
                queryMode: 'local',
                labelAlign: 'left',
                triggerAction: 'all',
                anchor: '100%',
                editable: false,
                readOnly: true,
                summaryDisplay: true,
                allowBlank: false,
                name: 'apiProvider',
                listeners: {
                    change: function (combo, value) {
                        var record = combo.getStore().findRecord('key', value);
                        this.buildApiProviderPanel(value, record.get('label'));
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
                    url: '/admin/formbuilder/output-workflow/api/get-api-provider',
                    fields: ['label', 'key'],
                    reader: {
                        type: 'json',
                        rootProperty: 'types'
                    },
                },
                listeners: {
                    load: function (store) {

                        var record,
                            value = this.data !== null && this.data.hasOwnProperty('apiProvider') ? this.data['apiProvider'] : null;

                        comboBox.setReadOnly(false);

                        if (value === null) {
                            return;
                        }

                        // no provider available. do nothing.
                        if (store.getCount() === 0) {
                            comboBox.setReadOnly(true);
                            return;
                        }

                        record = store.findRecord('key', value);

                        // api provider is not available anymore. reset value.
                        if (record === null) {
                            value = null;
                        }

                        comboBox.suspendEvents();
                        comboBox.setValue(value);
                        comboBox.resumeEvents(true);

                        if (value === null) {
                            return;
                        }

                        this.buildApiProviderPanel(value, record.get('label'));

                    }.bind(this)
                }
            });

        comboBox.setStore(store);

        return [comboBox]
    },

    buildApiProviderPanel: function (apiProvider, label) {

        if (this.apiProviderPanel !== null) {
            this.panel.remove(this.apiProviderPanel);
        }

        this.apiProviderPanel = new Ext.form.FieldSet({
            title: label,
            collapsible: false,
            collapsed: false,
            autoHeight: true,
            defaults: {
                labelWidth: 200
            },
            defaultType: 'textfield'
        });

        this.apiProviderPanel.add(this.generateDataMapperControlPanel());

        this.panel.add(this.apiProviderPanel);

        this.validateDataMapper();
    },

    generateDataMapperControlPanel: function () {

        var hasData = this.apiMappingData !== null,
            hasInconsistentData = this.mappingDataIsConsistent === false;

        return new Ext.Panel({
            layout: 'hbox',
            anchor: '100%',
            hidden: true,
            cls: 'form_builder_channel_api_data_mapper_control_panel',
            width: 350,
            hideLabel: true,
            autoHeight: true,
            items: [
                {
                    xtype: 'button',
                    iconCls: 'form_builder_output_workflow_channel_api_mapper',
                    text: t('form_builder.output_workflow.output_workflow_channel.api.start_api_mapping'),
                    style: 'background: #404f56; border-color: transparent;',
                    handler: this.showDataMappingEditor.bind(this)
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

    showDataMappingEditor: function () {

        var setupConfiguration = this.getValues(),
            addParams = {channelId: this.channelId},
            callbacks = {
                loadData: function () {
                    if (this.apiMappingData !== null) {
                        return {
                            configuration: this.apiConfiguration,
                            fields: this.apiMappingData
                        };
                    }

                    return null;

                }.bind(this),
                saveData: function (data) {
                    this.apiMappingData = data.fields;
                    this.apiConfiguration = data.configuration;
                    this.lastConsistentConfigHash = md5(Ext.encode(this.panel.form.getValues()));
                    this.mappingDataIsConsistent = true;
                    this.checkDataMappingEditorSignals();

                    Formbuilder.eventObserver.getObserver(this.formId).fireEvent('output_workflow.required_form_fields_refreshed', {workflowId: this.workflowId});

                }.bind(this)
            };

        new Formbuilder.extjs.extensions.formApiMappingEditor(this.formId, addParams, setupConfiguration, true, callbacks);
    },

    validateDataMapper: function () {

        var isValid = this.panel.form.isValid(),
            controlPanel = this.panel.query('panel[cls~="form_builder_channel_api_data_mapper_control_panel"]'),
            currentConfigHash = md5(Ext.encode(this.panel.form.getValues()));

        if (controlPanel.length === 0) {
            return;
        }

        if (this.apiMappingData !== null) {
            this.mappingDataIsConsistent = currentConfigHash === this.lastConsistentConfigHash
        } else {
            this.mappingDataIsConsistent = true;
        }

        this.checkDataMappingEditorSignals();

        controlPanel[0].setVisible(isValid);
    },

    checkDataMappingEditorSignals: function () {

        var hasData = this.apiMappingData !== null,
            hasInconsistentData = this.mappingDataIsConsistent === false,
            statusButtons = this.panel.query('panel[cls~="form_builder_channel_api_data_mapper_control_panel"] button[cls="form_builder_cme_status_button"]');

        if (statusButtons.length > 0) {
            setTimeout(function () {
                statusButtons[0].setStyle('background', hasData ? (hasInconsistentData ? '#d64517' : '#3e943e') : '#7f8a7f');
                statusButtons[0].setText(t('form_builder.output_workflow.output_workflow_channel.object.object_mapping.' + (hasData ? (hasInconsistentData ? 'status_inconsistent' : 'status_in_sync') : 'status_disabled')));
            }, 100)
        }
    },

    isValid: function () {
        return this.apiMappingData !== null && this.mappingDataIsConsistent === true && this.panel.form.isValid();
    },

    getValues: function () {

        var formValues = this.panel.form.getValues();

        formValues['apiMappingData'] = this.apiMappingData;
        formValues['apiConfiguration'] = this.apiConfiguration;

        return formValues;
    },

    getUsedFormFields: function () {

        if (this.apiMappingData === null) {
            return [];
        }

        return this.getRequiredFormFieldNamesRecursive(this.apiMappingData, []);
    },

    getRequiredFormFieldNamesRecursive: function (fields, fieldNames) {

        Ext.Array.each(fields, function (node) {

            var config = node.hasOwnProperty('config') && Ext.isObject(node.config) ? node.config : {},
                apiMapping = config.hasOwnProperty('apiMapping') && Ext.isArray(config.apiMapping) ? config.apiMapping : [];

            if (apiMapping.length > 0) {
                fieldNames.push(node.name)
            }

            if (node.hasOwnProperty('children') && Ext.isArray(node.children)) {
                this.getRequiredFormFieldNamesRecursive(node.children, fieldNames);
            }

        }.bind(this));

        return fieldNames;
    }
});