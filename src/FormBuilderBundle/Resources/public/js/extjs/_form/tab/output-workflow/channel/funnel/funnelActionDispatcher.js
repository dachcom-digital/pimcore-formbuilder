pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelActionDispatcher');
Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelActionDispatcher = Class.create({

    workflowId: null,
    channelName: null,
    funnelActionDefinition: null,
    data: null,

    button: null,
    eventListeners: null,

    window: null,
    windowActionPanel: null,
    funnelActionDataClass: null,

    store: null,

    initialize: function (workflowId, channelName, funnelActionDefinition, data) {

        this.workflowId = workflowId;
        this.channelName = channelName;
        this.funnelActionDefinition = funnelActionDefinition;
        this.data = data;

        this.button = null;
        this.eventListeners = null;

        this.window = null;
        this.windowActionPanel = null;
        this.funnelActionDataClass = null;
    },

    buildActionElement: function () {

        this.button = new Ext.Button({
            name: this.funnelActionDefinition.name,
            text: this.funnelActionDefinition.label,
            cls: this.funnelActionDefinition.label,
            handler: this.openFunnelActionWindow.bind(this),
            listeners: {
                render: this.bootActionChannel.bind(this),
                beforedestroy: this.shutDownActionChannel.bind(this),
            }
        });

        return this.button;
    },

    bootActionChannel: function () {

        this.initializeEventListener();

        if (this.getSelectedFunnelActionCoreConfig('type') !== null) {
            // initialize data class only
            this.generateFunnelActionPanel(this.data.type, true);
        }

        this.updateButtonState();
    },

    shutDownActionChannel: function () {

        if (this.eventListeners === null) {
            return;
        }

        this.eventListeners.destroy();
    },

    openFunnelActionWindow: function () {

        var funnelActionValue = this.getSelectedFunnelActionCoreConfig('type'),
            funnelActionAllowInvalidSubmissionValue = this.getSelectedFunnelActionCoreConfig('ignoreInvalidFormSubmission'),
            funnelActionAllowInvalidSubmission,
            funnelActionCombo,
            funnelActionStore;

        funnelActionAllowInvalidSubmission = new Ext.form.Checkbox({
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.funnel_action.ignore_invalid_form_submission'),
            name: 'ignoreInvalidFormSubmission',
            checked: funnelActionAllowInvalidSubmissionValue === true,
            uncheckedValue: false,
            inputValue: true,
            labelWidth: 200
        });

        funnelActionCombo = new Ext.form.ComboBox({
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.funnel_action.action'),
            name: 'funnelLayer',
            submitValue: false,
            labelWidth: 200,
            value: null,
            displayField: 'label',
            valueField: 'key',
            mode: 'local',
            queryMode: 'local',
            labelAlign: 'left',
            triggerAction: 'all',
            editable: false,
            summaryDisplay: true,
            emptyText: t('form_builder.output_workflow.output_workflow_channel.funnel_action.no_action'),
            allowBlank: true,
            disabled: true,
            listeners: {
                render: function (combo) {
                    combo.getStore().load();
                }.bind(this),
                change: function (combo, value) {
                    this.funnelActionDataClass = null;
                    this.generateFunnelActionPanel(value);
                }.bind(this)
            }
        });

        funnelActionStore = new Ext.data.Store({
            autoLoad: false,
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: '/admin/formbuilder/output-workflow/funnel-layer/get-actions',
                fields: ['label', 'key', 'configuration'],
                reader: {
                    type: 'json',
                    rootProperty: 'funnelActions'
                }
            },
            listeners: {
                load: function (store, records) {

                    if (records.length > 0) {
                        funnelActionCombo.setDisabled(false);
                    }

                    funnelActionCombo.setValue(funnelActionValue);

                }.bind(this)
            }
        });

        funnelActionCombo.setStore(funnelActionStore);

        this.windowActionPanel = new Ext.form.Panel({
            title: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.funnel_action_configuration'),
            bodyStyle: 'padding: 10px',
            border: false,
        });

        this.window = new Ext.Window({
            width: 600,
            height: 400,
            iconCls: 'pimcore_icon_output_workflow_funnel',
            closeAction: 'destroy',
            modal: true,
            plain: true,
            autoScroll: true,
            autoHeight: true,
            preventRefocus: true,
            title: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.funnel_action'),
            items: [
                {
                    xtype: 'panel',
                    bodyStyle: 'padding: 10px',
                    border: false,
                    items: [
                        funnelActionCombo,
                        funnelActionAllowInvalidSubmission,
                        this.windowActionPanel
                    ]
                }
            ],
            buttons: [
                {
                    text: t('OK'),
                    handler: this.saveAction.bind(this),
                    iconCls: 'pimcore_icon_save',
                },
                {
                    text: t('cancel'),
                    iconCls: 'pimcore_icon_cancel',
                    handler: function () {
                        this.window.close();
                    }.bind(this)
                }
            ]
        });

        this.window.show();
    },

    generateFunnelActionPanel: function (funnelActionType, initDataClassOnly) {

        var items;

        if (this.windowActionPanel !== null) {
            this.windowActionPanel.removeAll();
        }

        if (this.funnelActionDataClass === null) {
            this.funnelActionDataClass = funnelActionType === null ? null : this.createFunnelActionDataClass(funnelActionType);
        }

        Formbuilder.eventObserver.getObserver('ow' + this.workflowId).fireEvent('output_workflow.channel.request.list', this.refreshChannels.bind(this));

        if (initDataClassOnly === true) {
            return;
        }

        items = this.funnelActionDataClass !== null
            ? this.funnelActionDataClass.getConfigItems()
            : [{
                xtype: 'tbtext',
                style: 'padding: 10px 10px 10px 0',
                text: 'No configuration for "' + funnelActionType + '" found.',
            }];

        this.windowActionPanel.add(items);
    },

    createFunnelActionDataClass: function (funnelActionType) {

        var funnelActionDataClass,
            funnelActionConfig = this.assertFunnelActionDataClassConfiguration(funnelActionType);

        if (typeof Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction !== 'object') {
            return null;
        }

        if (typeof Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction[funnelActionType] === 'undefined') {
            return null;
        }

        funnelActionDataClass = new Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction[funnelActionType](
            this.button,
            funnelActionType,
            funnelActionConfig,
        );

        return funnelActionDataClass;
    },

    assertFunnelActionDataClassConfiguration: function (funnelActionType) {

        if (this.data === null) {
            return null;
        }

        if (funnelActionType !== this.data.type) {
            this.data = null;

            return null;
        }

        return this.data.hasOwnProperty('configuration') ? this.data.configuration : null;
    },

    getSelectedFunnelActionCoreConfig: function (slot) {

        if (this.data === null) {
            return null;
        }

        if (slot === 'type') {
            return this.data.hasOwnProperty('type') ? this.data.type : null
        }

        if (!this.data.hasOwnProperty('coreConfiguration')) {
            return null;
        }

        return this.data.coreConfiguration.hasOwnProperty(slot) ? this.data.coreConfiguration[slot] : null
    },

    isValid: function () {

        if (this.funnelActionDataClass === null) {
            return false;
        }

        return this.funnelActionDataClass.isValid() === true;
    },

    saveAction: function (btn) {

        var coreConfiguration = {},
            window = btn.up('window');

        coreConfiguration['ignoreInvalidFormSubmission'] = window.query('checkbox[name="ignoreInvalidFormSubmission"]')[0].getValue()

        if (this.isValid() === false) {
            Ext.MessageBox.alert(t('error'), t('form_builder.output_workflow.output_workflow_channel.funnel_action.invalid_action'));

            return;
        }

        this.data = {
            type: this.funnelActionDataClass.getType(),
            coreConfiguration: coreConfiguration,
            configuration: this.funnelActionDataClass.getActionData(),
            triggerName: this.funnelActionDefinition.name,
        };

        this.window.close();
    },

    getActionData: function () {
        return this.data
    },

    initializeEventListener: function () {

        var owEventObserver = Formbuilder.eventObserver.getObserver('ow' + this.workflowId);

        this.eventListeners = owEventObserver.on({
            'output_workflow.channel.add': function () {
                owEventObserver.fireEvent('output_workflow.channel.request.list', this.refreshChannels.bind(this));
            }.bind(this),
            'output_workflow.channel.remove': function () {
                owEventObserver.fireEvent('output_workflow.channel.request.list', this.refreshChannels.bind(this));
            }.bind(this),
            'output_workflow.channel.rename': function () {
                owEventObserver.fireEvent('output_workflow.channel.request.list', this.refreshChannels.bind(this));
            }.bind(this),
            destroyable: true
        });
    },

    refreshChannels: function (channelPanelConfigClasses) {

        var items = [];

        if (this.funnelActionDataClass === null) {
            return;
        }

        Ext.each(channelPanelConfigClasses, function (channelPanelConfigClass) {

            var channelName = channelPanelConfigClass.dataClass.getName(),
                label = channelName + ' [' + channelPanelConfigClass.dataClass.getType() + ']';

            if (channelPanelConfigClass.dataClass.getName() !== this.channelName) {
                items.push({channelName: channelName, label: label});
            }

        }.bind(this));

        this.funnelActionDataClass.setAvailableChannels(items);
    },

    updateButtonState: function () {

        if (this.funnelActionDataClass !== null) {
            return;
        }

        this.button.setText(this.button.cls);
        this.button.setIconCls('pimcore_icon_warning');

    }
});