pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.abstractLayer');
Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.abstractLayer = Class.create({

    funnelLayerType: null,
    funnelLayerOptions: null,
    funnelLayerConfig: null,
    workflowId: null,
    channelId: null,

    initialize: function (funnelLayerType, funnelLayerOptions, funnelLayerConfig, workflowId, channelId) {
        this.funnelLayerType = funnelLayerType;
        this.funnelLayerOptions = funnelLayerOptions;
        this.funnelLayerConfig = funnelLayerConfig;
        this.workflowId = workflowId;
        this.channelId = channelId;
    },

    getType: function () {
        return this.funnelLayerType;
    },

    getConfigItems: function () {
        return [];
    }
});