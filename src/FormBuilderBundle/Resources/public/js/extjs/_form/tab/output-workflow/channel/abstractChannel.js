pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel');
Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel = Class.create({

    type: null,
    data: null,
    formId: null,
    workflowId: null,
    channelId: null,
    panel: null,

    initialize: function (type, data, formId, workflowId) {
        this.type = type;
        this.formId = formId;
        this.workflowId = workflowId;
        this.channelId = data && data.hasOwnProperty('id') ? data.id : null;
        this.data = data && data.hasOwnProperty('configuration') ? data.configuration : null;
    },

    getType: function () {
        return this.type;
    },

    getId: function () {
        return 'output_channel_' + this.getType() + '_' + Ext.id();
    },

    generateLocalizedFieldBlock: function (callBack) {
        var localizedField = new Formbuilder.extjs.types.localizedField(callBack, true);

        return localizedField.getField();
    },

    /**
     * @abstract
     */
    isValid: function () {
        return false;
    },

    /**
     * @abstract
     */
    getValues: function () {
        return null;
    },

    /**
     * @abstract
     *
     * Needs to return an array with all required form fields (name)
     *
     * @returns {Array}
     */
    getUsedFormFields: function () {
        return [];
    }
});