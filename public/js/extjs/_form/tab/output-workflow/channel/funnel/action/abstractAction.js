pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.abstractAction');
Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.abstractAction = Class.create({

    actionButton: null,
    type: null,
    data: null,
    availableChannels: null,

    initialize: function (actionButton, type, data) {
        this.actionButton = actionButton;
        this.type = type;
        this.data = data;
        this.availableChannels = null;
    },

    getType: function () {
        return this.type;
    },

    setAvailableChannels(channels) {
        this.availableChannels = channels;
    },

    getActionData: function () {
        return [];
    },

    getConfigItems: function () {
        return [];
    },

    isValid: function () {
        return false;
    },

    updateButtonState: function () {
        this.actionButton.setText(this.actionButton.cls + ' (Unknown Action)');
        this.actionButton.setIconCls('pimcore_icon_warning');
    }
});