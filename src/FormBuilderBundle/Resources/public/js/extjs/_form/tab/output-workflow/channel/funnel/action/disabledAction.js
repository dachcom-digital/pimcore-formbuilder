pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.disabledAction');
Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.disabledAction = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.abstractAction, {

    initialize: function ($super, actionButton, type, data) {

        $super(actionButton, type, data);

        this.updateButtonState();
    },

    getActionData: function () {

        this.updateButtonState();

        return this.data;
    },

    getConfigItems: function () {
        return [{
            xtype: 'tbtext',
            style: 'padding: 10px 10px 10px 0',
            text: 'No configuration available.',
        }];
    },

    isValid: function () {
        return true;
    },

    updateButtonState: function () {
        this.actionButton.setText(this.actionButton.cls + ' (Disabled)');
        this.actionButton.setIconCls('pimcore_icon_hide');
    }
});