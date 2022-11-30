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
            xtype: 'checkbox',
            name: 'populateForm',
            fieldLabel: t('form_builder.output_workflow.output_workflow_channel.funnel_action.return_to_form.populate_form'),
            checked: this.data && this.data.hasOwnProperty('populateForm') ? this.data.populateForm === true : false,
            listeners: {
                change: function (checkbox, value) {
                    this.data = {
                        populateForm: value
                    }
                }.bind(this)
            }
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