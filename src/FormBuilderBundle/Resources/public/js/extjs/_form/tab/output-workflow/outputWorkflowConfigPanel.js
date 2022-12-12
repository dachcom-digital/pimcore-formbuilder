pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel');
pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.configPanel');
Formbuilder.extjs.formPanel.outputWorkflow.configPanel = Class.create({

    parentPanel: null,

    panel: null,
    channelPanel: null,
    channelSuccessManagementPanel: null,
    funnelNagPanel: null,
    channelPanelConfigClasses: null,

    workflowData: null,
    formId: null,
    outputWorkflowChannelStore: null,
    outputWorkflowChannelVirtualFunnelActionDefinitions: null,
    outputWorkflowChannels: null,
    outputWorkflowSuccessManagementData: null,

    initialize: function (workflowData, formId, parentPanel) {
        this.channelPanelConfigClasses = [];
        this.parentPanel = parentPanel;
        this.workflowData = workflowData;
        this.formId = formId;
        this.outputWorkflowChannelStore = this.workflowData.hasOwnProperty('output_workflow_channels_store') ? this.workflowData.output_workflow_channels_store : [];
        this.outputWorkflowChannelVirtualFunnelActionDefinitions = this.workflowData.hasOwnProperty('output_workflow_channels_virtual_funnel_action_definitions') ? this.workflowData.output_workflow_channels_virtual_funnel_action_definitions : [];
        this.outputWorkflowChannels = this.workflowData.hasOwnProperty('output_workflow_channels') ? this.workflowData.output_workflow_channels : [];
        this.outputWorkflowSuccessManagementData = this.workflowData.hasOwnProperty('output_workflow_success_management') ? this.workflowData.output_workflow_success_management : {};

        Formbuilder.eventObserver.registerObservable('ow' + this.workflowData.id);
    },

    getLayout: function () {

        var observerListener,
            observerChannelListener;

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

                this.getFunnelNagPanel(),
                this.getOutputSuccessManagementPanel(),
                this.getOutputChannelPanel()
            ]
        });

        observerListener = Formbuilder.eventObserver.getObserver(this.formId).on(
            'output_workflow.required_form_fields_requested',
            this.getUsedFormFields.bind(this),
            null, {destroyable: true}
        );

        observerChannelListener = Formbuilder.eventObserver.getObserver('ow' + this.workflowData.id).on(
            'output_workflow.channel.request.list',
            function (listModifier) {
                listModifier.call(this, this.channelPanelConfigClasses);
                return true;
            }.bind(this),
            null, {destroyable: true}
        );

        this.panel.on('beforedestroy', function () {
            observerListener.destroy();
            observerChannelListener.destroy();
            Formbuilder.eventObserver.unregisterObservable('ow' + this.workflowData.id);
            this.channelPanelConfigClasses = null;
        }.bind(this));

        return this.panel;
    },

    isFunnelOutputWorkflow: function () {
        return this.workflowData.funnel_workflow === true;
    },

    getFunnelNagPanel: function () {

        if (this.isFunnelOutputWorkflow() === false) {
            return [];
        }

        this.funnelNagPanel = new Ext.form.Panel({
            iconCls: 'pimcore_icon_output_workflow_funnel',
            title: t('form_builder.output_workflow.output_workflow_channel_funnel_workflow'),
            autoScroll: true,
            border: false,
            items: [
                {
                    xtype: 'label',
                    style: 'display: block; padding: 10px 0;',
                    html: t('form_builder.output_workflow.output_workflow_channel_funnel_workflow_description')
                }
            ]
        });

        return this.funnelNagPanel;
    },

    getOutputSuccessManagementPanel: function () {

        var fieldId = Ext.id(),
            successMessageToggleComponent,
            componentConfiguration = {
                identifier: 'successManagement',
                sectionId: this.sectionId,
                index: this.index,
                onGenerateFieldName: function (elementType) {
                    return elementType;
                }.bind(this),
                onGenerateTopBar: function () {
                    return [];
                }.bind(this)
            };

        if (this.isFunnelOutputWorkflow() === true) {
            return [];
        }

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

    getDeleteControl: function (data, channelConfig, channelDataClass) {

        var
            items = [],
            label,
            toolbar;

        label = channelConfig.hasOwnProperty('label') ? '<strong>' + channelConfig.label + '</strong>' : ('Channel ' + index);

        if (channelDataClass !== null && this.isFunnelOutputWorkflow()) {
            label = label + ' (<em>' + channelDataClass.getName() + '</em>)';
        }

        if (channelDataClass !== null && this.isFunnelOutputWorkflow()) {
            items.push({
                xtype: 'button',
                iconCls: 'pimcore_icon_edit',
                handler: function () {

                    Ext.MessageBox.prompt(
                        t('form_builder.output_workflow.output_workflow_channel_rename'),
                        t('form_builder.output_workflow.output_workflow_channel_rename_set'),
                        function (button, value) {

                            var items = toolbar.query('tbtext');

                            if (button === 'cancel') {
                                return false;
                            }

                            channelDataClass.setName(value);

                            if (items.length > 0) {
                                items[0].setHtml(value);
                            }

                            this.fireOutputWorkflowObserverEvent('output_workflow.channel.rename', {channelDataClass: channelDataClass});
                        }.bind(this),
                        null, null, channelDataClass.getName()
                    );
                }.bind(this)
            });
        }

        items.push({
            xtype: 'tbtext',
            html: label,
            iconCls: channelConfig.hasOwnProperty('icon_class') ? channelConfig.icon_class : 'pimcore_icon_output_workflow_channel',
        });

        items.push('->');

        items.push({
            cls: 'pimcore_block_button_minus',
            iconCls: 'pimcore_icon_minus',
            listeners: {
                click: this.removeOutputChannel.bind(this)
            }
        });

        return toolbar = new Ext.Toolbar({
            items: items
        });
    },

    removeOutputChannel: function (btn) {

        var panel = btn.up('panel'),
            removedChannelId = null;

        Ext.each(this.channelPanelConfigClasses, function (channelWrapper) {
            if (channelWrapper.id === panel.id) {
                removedChannelId = channelWrapper.id;
                Ext.Array.remove(this.channelPanelConfigClasses, channelWrapper);
                return false;
            }
        }.bind(this));

        this.channelPanel.remove(panel);

        this.fireObserverEvent('output_workflow.required_form_fields_refreshed', {workflowId: this.workflowData.id});
        this.fireOutputWorkflowObserverEvent('output_workflow.channel.remove', {removedChannelId: removedChannelId});
    },

    addOutputChannel: function (data, channelConfig) {

        var element,
            items,
            channelPanelConfigId = null,
            channelPanelConfigClass = null,
            channelDataClass = this.createChannelConfigPanel(data, channelConfig);

        if (channelDataClass !== null) {

            channelPanelConfigId = channelDataClass.getId();
            channelPanelConfigClass = {id: channelPanelConfigId, dataClass: channelDataClass}

            items = Ext.Array.merge(
                [channelDataClass.getLayout()],
                this.isFunnelOutputWorkflow()
                    ? channelDataClass.getFunnelActionLayout()
                    : []
            );

            this.channelPanelConfigClasses.push(channelPanelConfigClass);

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
            tbar: this.getDeleteControl(data, channelConfig, channelDataClass),
            items: items,
            listeners: {
                render: function () {

                    var virtualFunnelActionDefinitions = this.isFunnelOutputWorkflow() && channelDataClass !== null && channelDataClass.isVirtualFunnelAware()
                        ? channelDataClass.getVirtualFunnelActionDefinitions()
                        : [];

                    if (virtualFunnelActionDefinitions.length > 0 && channelDataClass !== null) {
                        channelDataClass.populateFunnelActions(virtualFunnelActionDefinitions, false);
                    }
                }.bind(this)
            }
        });

        this.channelPanel.add(element);

        if (this.panel) {
            this.panel.updateLayout();
        }

        this.fireOutputWorkflowObserverEvent('output_workflow.channel.add', {channelDataClass: channelDataClass});
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

        channelConfigPanel.setVirtualFunnelAware(this.isFunnelOutputWorkflow() && channelIdentifier !== 'funnel');
        channelConfigPanel.setVirtualFunnelActionDefinitions(this.outputWorkflowChannelVirtualFunnelActionDefinitions);

        return channelConfigPanel;
    },

    saveOutputChannel: function (ev) {

        var errorMessage = null,
            channelData = [],
            hasInvalidConfigChannel = false,
            successManagementData = [],
            formData;

        Ext.each(this.channelPanelConfigClasses, function (channelWrapper) {

            var transposedData,
                compiledData = {},
                funnelActions = null,
                dataClass = channelWrapper.dataClass;

            if (dataClass.isValid()) {

                transposedData = DataObjectParser.transpose(dataClass.getValues());

                compiledData['type'] = dataClass.getType();
                compiledData['name'] = dataClass.getName();
                compiledData['configuration'] = transposedData.data();

                if (this.isFunnelOutputWorkflow() === true) {

                    if (dataClass.funnelActionsValid()) {
                        funnelActions = dataClass.getFunnelActionDefinitionData();
                    } else {
                        errorMessage = t('form_builder.output_workflow.output_workflow_channel.funnel_action.invalid_action');
                        hasInvalidConfigChannel = true;
                        return false;
                    }
                }

                compiledData['funnelActions'] = funnelActions;

                channelData.push(compiledData);
            } else {
                hasInvalidConfigChannel = true;
                return false;
            }
        }.bind(this));

        if (hasInvalidConfigChannel === true) {
            Ext.Msg.alert(t('error'), errorMessage !== null ? errorMessage : t('form_builder.output_workflow.output_workflow_channel_invalid_configuration'));
            return;
        }

        if (this.channelSuccessManagementPanel !== null) {
            successManagementData = DataObjectParser.transpose(this.channelSuccessManagementPanel.getForm().getValues());
            successManagementData = successManagementData.data();
        }

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
    },

    fireOutputWorkflowObserverEvent: function (name, data) {
        Formbuilder.eventObserver.getObserver('ow' + this.workflowData.id).fireEvent(name, data);
    }
});