pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.returnToFormAction');
Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.returnToFormAction = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelAction.abstractAction, {

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
        this.actionButton.setText(this.actionButton.cls + ' (Return To Form)');
        this.actionButton.setIconCls('pimcore_icon_save');
    }
});