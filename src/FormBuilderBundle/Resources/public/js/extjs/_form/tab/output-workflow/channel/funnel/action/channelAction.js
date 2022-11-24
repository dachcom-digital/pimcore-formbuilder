pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.channelAction');
Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.channelAction = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.abstractAction, {

    initialize: function ($super, actionButton, type, data) {

        $super(actionButton, type, data);

        this.initializeStore();
    },

    setAvailableChannels: function($super, channels) {

        $super(channels);

        if (this.store === null) {
            return;
        }

        if (!Ext.isArray(this.availableChannels)) {
            return;
        }

        this.store.loadData(this.availableChannels);
    },

    getActionData: function () {

        this.updateButtonState();

        return this.data;
    },

    getConfigItems: function () {

        return [{
            xtype: 'combo',
            name: 'channelName',
            store: this.store,
            triggerAction: 'all',
            anchor: '100%',
            editable: false,
            queryMode: 'local',
            valueField: 'channelName',
            displayField: 'label',
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.funnel_channel_relation'),
            listeners: {
                render: this.onChannelComboRender.bind(this),
                change: this.onChannelChange.bind(this)
            }
        }];
    },

    isValid: function () {

        if(this.data === null) {
            return false;
        }

        return this.data.selectedChannelName !== null;
    },

    onChannelComboRender: function (combo) {

        var record, value = this.getSelectedChannelName();

        record = value !== null
            ? this.store.findRecord('channelName', value, undefined, undefined, undefined, true)
            : null;

        if (record === null) {
            value = null;
        }

        combo.setValue(value);
    },

    onChannelChange: function (combo, value) {

        this.data = {
            channelName: value
        }
    },

    initializeStore: function () {

        this.store = new Ext.data.Store({
            autoLoad: false,
            fields: ['channelName', 'label'],
            data: [],
            listeners: {
                refresh: this.updateButtonState.bind(this)
            }
        });
    },

    updateButtonState: function () {

        var record, selectedChannelName;

        if (this.store === null) {
            return null;
        }

        selectedChannelName = this.getSelectedChannelName();

        if (selectedChannelName !== null) {

            record = this.store.findRecord('channelName', selectedChannelName, undefined, undefined, undefined, true);

            if (record !== null) {

                this.actionButton.setText(this.actionButton.cls + ' (' + record.get('channelName') + ')');
                this.actionButton.setIconCls('pimcore_icon_save');

                return;
            }
        }

        this.data = null;

        this.actionButton.setText(this.actionButton.cls);
        this.actionButton.setIconCls('pimcore_icon_warning');
    },

    getSelectedChannelName: function () {

        if (this.data === null) {
            return null;
        }

        return this.data.hasOwnProperty('channelName') ? this.data.channelName : null
    },
});