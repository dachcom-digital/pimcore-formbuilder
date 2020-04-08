pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel');
pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.configPanel');
Formbuilder.extjs.formPanel.outputWorkflow.configPanel = Class.create({

    parentPanel: null,

    panel: null,
    channelPanel: null,
    channelSuccessManagementPanel: null,
    channelPanelConfigClasses: null,

    workflowData: null,
    formId: null,
    outputWorkflowChannelStore: null,
    outputWorkflowChannels: null,
    outputWorkflowSuccessManagementData: null,

    initialize: function (workflowData, formId, parentPanel) {
        this.channelPanelConfigClasses = [];
        this.parentPanel = parentPanel;
        this.workflowData = workflowData;
        this.formId = formId;
        this.outputWorkflowChannelStore = this.workflowData.hasOwnProperty('output_workflow_channels_store') ? this.workflowData.output_workflow_channels_store : [];
        this.outputWorkflowChannels = this.workflowData.hasOwnProperty('output_workflow_channels') ? this.workflowData.output_workflow_channels : [];
        this.outputWorkflowSuccessManagementData = this.workflowData.hasOwnProperty('output_workflow_success_management') ? this.workflowData.output_workflow_success_management : {};
    },

    getLayout: function () {

        var observerListener;

        this.panel = new Ext.form.FormPanel({
            title: t('form_builder.tab.output_workflow') + ' "' + this.workflowData.name + '"',
            border: false,
            tools: [
                {
                    xtype: 'tbfill'
                },
                {
                    xtype: 'button',
                    text: t('form_builder.output_workflow.cancel_close'),
                    iconCls: 'pimcore_icon_cancel',
                    handler: function (btn) {
                        this.fireObserverEvent('output_workflow.required_form_fields_reset', {workflowId: this.workflowData.id});
                        this.parentPanel.clearEditPanel();
                    }.bind(this)
                },
                {
                    xtype: 'button',
                    text: t('save'),
                    iconCls: 'pimcore_icon_save',
                    handler: this.saveOutputChannel.bind(this)
                }
            ],
            defaults: {
                labelWidth: 200
            },
            items: [
                {
                    xtype: 'panel',
                    bodyStyle: 'padding: 10px;',
                    items: [
                        {
                            xtype: 'textfield',
                            width: 600,
                            name: 'output_workflow_name',
                            fieldLabel: t('name'),
                            value: this.workflowData.name
                        },
                    ]
                },

                this.getOutputSuccessManagementPanel(),
                this.getOutputChannelPanel()
            ]
        });

        observerListener = Formbuilder.eventObserver.getObserver(this.formId).addListener(
            'output_workflow.required_form_fields_requested',
            this.getUsedFormFields.bind(this),
            null, {destroyable: true}
        );

        this.panel.on('beforedestroy', function () {
            observerListener.destroy();
            this.channelPanelConfigClasses = null;
        }.bind(this));

        return this.panel;
    },

    getOutputSuccessManagementPanel: function () {

        var fieldId = Ext.id(),
            successMessageToggleComponent,
            componentConfiguration = {
                identifier: 'successManagement',
                sectionId: this.sectionId,
                index: this.index,
                onGenerateFieldName: function (elementType, args, el) {
                    return elementType;
                }.bind(this),
                onGenerateTopBar: function () {
                    return [];
                }.bind(this)
            };

        successMessageToggleComponent = new Formbuilder.extjs.components.successMessageToggleComponent(fieldId, componentConfiguration, this.outputWorkflowSuccessManagementData, true);
        successMessageToggleComponent.setBodyStyle('');

        this.channelSuccessManagementPanel = new Ext.form.Panel({
            iconCls: 'pimcore_icon_output_workflow_channel',
            title: t('form_builder.output_workflow.output_workflow_channel_success_management'),
            autoScroll: true,
            border: false,
            items: [
                {
                    xtype: 'label',
                    style: 'display: block; padding: 10px 0;',
                    html: t('form_builder.output_workflow.output_workflow_channel_success_management_description')
                },
                successMessageToggleComponent.getLayout()
            ]
        });

        return this.channelSuccessManagementPanel;
    },

    getOutputChannelPanel: function () {

        this.channelPanel = new Ext.Panel({
            iconCls: 'pimcore_icon_output_workflow_channel',
            style: 'margin-top: 20px',
            title: t('form_builder.output_workflow.output_workflow_channel_configuration'),
            autoScroll: true,
            border: false,
            items: [
                this.getAddControl()
            ]
        });

        Ext.Array.each(this.outputWorkflowChannels, function (channel) {

            var configuration = Ext.Array.filter(this.outputWorkflowChannelStore, function (item) {
                return item.identifier === channel.type;
            });

            if (configuration.length !== 1) {
                throw 'invalid or no configuration found';
            }

            this.addOutputChannel(channel, configuration[0]);
        }.bind(this));

        return this.channelPanel;
    },

    getAddControl: function () {

        var classMenu = [],
            items = [],
            availableChannelStore = this.outputWorkflowChannelStore;

        Ext.Array.each(availableChannelStore, function (channelConfig) {
            classMenu.push({
                text: channelConfig.hasOwnProperty('label') ? channelConfig.label : ('Channel ' + index),
                iconCls: channelConfig.hasOwnProperty('icon_class') ? channelConfig.icon_class : 'pimcore_icon_output_workflow_channel',
                handler: this.addOutputChannel.bind(this, null, channelConfig)
            });
        }.bind(this));

        if (availableChannelStore.length === 1) {
            items.push({
                cls: 'pimcore_block_button_plus',
                text: t(classMenu[0].text),
                iconCls: 'pimcore_icon_plus',
                handler: classMenu[0].handler
            });
        } else if (availableChannelStore.length > 1) {
            items.push({
                cls: 'pimcore_block_button_plus',
                iconCls: 'pimcore_icon_plus',
                menu: classMenu
            });
        }

        return new Ext.Toolbar({
            items: items
        });
    },

    getDeleteControl: function (data, channelConfig) {

        var items = [{
            xtype: 'tbtext',
            html: channelConfig.hasOwnProperty('label') ? '<strong>' + channelConfig.label + '</strong>' : ('Channel ' + index),
            iconCls: channelConfig.hasOwnProperty('icon_class') ? channelConfig.icon_class : 'pimcore_icon_output_workflow_channel',
        }];

        items.push('->');

        items.push({
            cls: 'pimcore_block_button_minus',
            iconCls: 'pimcore_icon_minus',
            listeners: {
                'click': this.removeOutputChannel.bind(this)
            }
        });

        return new Ext.Toolbar({
            items: items
        });
    },

    removeOutputChannel: function (btn) {
        var panel = btn.up('panel');

        Ext.each(this.channelPanelConfigClasses, function (channelWrapper) {
            if (channelWrapper.id === panel.id) {
                Ext.Array.remove(this.channelPanelConfigClasses, channelWrapper);
                return false;
            }
        }.bind(this));

        this.channelPanel.remove(panel);

        this.fireObserverEvent('output_workflow.required_form_fields_refreshed', {workflowId: this.workflowData.id});
    },

    addOutputChannel: function (data, channelConfig) {

        var element,
            items = [],
            channelPanelConfigId = null,
            channelPanelConfig = this.createChannelConfigPanel(data, channelConfig);

        if (channelPanelConfig !== null) {
            channelPanelConfigId = channelPanelConfig.getId();
            this.channelPanelConfigClasses.push({id: channelPanelConfigId, dataClass: channelPanelConfig});
            items = [channelPanelConfig.getLayout()]
        } else {
            items = [{
                xtype: 'tbtext',
                text: 'No configuration for ' + channelConfig.identifier + ' found.',
            }]
        }

        element = new Ext.Panel({
            style: 'margin-top: 10px',
            bodyStyle: 'padding:10px;',
            autoHeight: true,
            border: true,
            id: channelPanelConfigId,
            tbar: this.getDeleteControl(data, channelConfig),
            items: items
        });

        this.channelPanel.add(element);

        if (this.panel) {
            this.panel.updateLayout();
        }
    },

    createChannelConfigPanel: function (data, channelConfig) {

        var channelIdentifier = channelConfig.identifier, channelConfigPanel;

        if (typeof Formbuilder.extjs.formPanel.outputWorkflow.channel !== 'object') {
            return null;
        }

        if (typeof Formbuilder.extjs.formPanel.outputWorkflow.channel[channelIdentifier] === 'undefined') {
            return null;
        }

        channelConfigPanel = new Formbuilder.extjs.formPanel.outputWorkflow.channel[channelIdentifier](channelIdentifier, data, this.formId, this.workflowData.id);

        return channelConfigPanel;
    },

    saveOutputChannel: function (ev) {

        var channelData = [],
            hasInvalidConfigChannel = false,
            successManagementData,
            formData;

        Ext.each(this.channelPanelConfigClasses, function (channelWrapper) {
            var transposedData, compiledData = {}, dataClass = channelWrapper.dataClass;
            if (dataClass.isValid()) {
                transposedData = DataObjectParser.transpose(dataClass.getValues());
                compiledData['configuration'] = transposedData.data();
                compiledData['type'] = dataClass.getType();
                channelData.push(compiledData);
            } else {
                hasInvalidConfigChannel = true;
                return false;
            }
        }.bind(this));

        if (hasInvalidConfigChannel === true) {
            Ext.Msg.alert(t('error'), t('form_builder.output_workflow.output_workflow_channel_invalid_configuration'));
            return;
        }

        successManagementData = DataObjectParser.transpose(this.channelSuccessManagementPanel.getForm().getValues());
        successManagementData = successManagementData.data();

        formData = {
            name: this.panel.getForm().findField('output_workflow_name').getValue(),
            successManagement: successManagementData,
            channels: channelData
        };

        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/save-output-workflow/' + this.workflowData.id,
            method: 'post',
            params: {
                data: Ext.encode(formData)
            },
            success: this.saveOnComplete.bind(this),
            failure: this.saveOnError.bind(this)
        });
    },

    getUsedFormFields: function () {

        var usedFormFields = [];
        Ext.each(this.channelPanelConfigClasses, function (channelWrapper) {
            usedFormFields = Ext.Array.merge(channelWrapper.dataClass.getUsedFormFields(), usedFormFields);
        }.bind(this));

        this.fireObserverEvent('output_workflow.required_form_fields_updated', {
            fields: usedFormFields,
            workflowId: this.workflowData.id
        });
    },

    saveOnComplete: function (response) {

        var res = Ext.decode(response.responseText);

        if (res.success === false) {
            pimcore.helpers.showNotification(t('error'), res.message, 'error');
            return;
        }

        this.parentPanel.tree.getStore().load();

        this.fireObserverEvent('output_workflow.required_form_fields_persisted', {workflowId: this.workflowData.id});

        pimcore.helpers.showNotification(t('success'), t('form_builder.output_workflow.output_workflow_channel.save_successful'), 'success');
    },

    saveOnError: function () {
        pimcore.helpers.showNotification(t('error'), t('form_builder.output_workflow.output_workflow_channel.save_failed'), 'error');
    },

    fireObserverEvent: function (name, data) {
        Formbuilder.eventObserver.getObserver(this.formId).fireEvent(name, data);
    }
});