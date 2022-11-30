pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel');
Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel = Class.create({

    internalId: null,
    type: null,
    data: null,
    funnelActions: null,
    formId: null,
    workflowId: null,
    channelId: null,
    channelName: null,
    panel: null,

    virtualFunnelAware: false,
    virtualFunnelActionDefinitions: [],
    funnelActionLayout: null,
    funnelActionDispatcherDataClasses: null,

    initialize: function (type, data, formId, workflowId) {

        var uuidGenerator;

        this.type = type;
        this.formId = formId;
        this.workflowId = workflowId;
        this.channelId = data && data.hasOwnProperty('id') ? data.id : null;
        this.channelName = data && data.hasOwnProperty('name') ? data.name : null;
        this.data = data && data.hasOwnProperty('configuration') ? data.configuration : null;
        this.funnelActions = data && data.hasOwnProperty('funnelActions') ? data.funnelActions : null;

        this.virtualFunnelAware = false;
        this.virtualFunnelActionDefinitions = [];
        this.funnelActionLayout = null;
        this.funnelActionDispatcherDataClasses = null;

        if (this.channelName === null) {
            uuidGenerator = new Ext.data.identifier.Uuid();
            this.channelName = uuidGenerator.generate();
        }

        this.internalId =  'output_channel_' + this.getType() + '_' + Ext.id();
    },

    setVirtualFunnelAware: function (funnelAware) {
        this.virtualFunnelAware = funnelAware;
    },

    isVirtualFunnelAware: function () {
        return this.virtualFunnelAware;
    },

    getFunnelActions: function () {
        return this.funnelActions;
    },

    funnelActionsValid: function() {

        var valid = true;

        if (this.funnelActionDispatcherDataClasses === null) {
            return false;
        }

        Ext.Array.each(this.funnelActionDispatcherDataClasses, function (funnelActionDispatcherDataClass) {
            if (funnelActionDispatcherDataClass.isValid() === false) {
                valid = false;
                return false;
            }
        });

        return valid;
    },

    getFunnelActionDefinitionData: function () {

        var data = [];

        if (this.funnelActionDispatcherDataClasses === null) {
            return null;
        }

        Ext.Array.each(this.funnelActionDispatcherDataClasses, function(funnelActionDispatcherDataClass) {
            data.push(funnelActionDispatcherDataClass.getActionData());
        })

        return data;
    },

    setVirtualFunnelActionDefinitions: function (virtualFunnelActionDefinitions) {
        this.virtualFunnelActionDefinitions = virtualFunnelActionDefinitions;
    },

    getVirtualFunnelActionDefinitions: function () {
        return this.virtualFunnelActionDefinitions;
    },

    populateFunnelActions: function (funnelActionDefinitions, clearActionLayout) {

        var funnelActions = [],
            funnelActionDispatcherDataClasses = [];

        if (this.funnelActionLayout === null) {
            return;
        }

        if (clearActionLayout === true) {
            this.funnelActionLayout.removeAll();
        }

        Ext.Array.each(funnelActionDefinitions, function (funnelActionDefinition) {

            var funnelActionDispatcherDataClass,
                funnelActionConfig = null;

            if (this.getFunnelActions() !== null) {
                Ext.Array.each(this.getFunnelActions(), function (funnelAction) {
                    if (funnelAction.triggerName === funnelActionDefinition.name) {
                        funnelActionConfig = funnelAction;
                        return false;
                    }
                });
            }

            funnelActionDispatcherDataClass = new Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelActionDispatcher(
                this.workflowId,
                this.channelName,
                funnelActionDefinition,
                funnelActionConfig
            );

            funnelActions.push(funnelActionDispatcherDataClass.buildActionElement());
            funnelActionDispatcherDataClasses.push(funnelActionDispatcherDataClass);

        }.bind(this))

        if (funnelActions.length === 0) {
            this.funnelActionDispatcherDataClasses = null;

            return [];
        }

        this.funnelActionDispatcherDataClasses = funnelActionDispatcherDataClasses;
        this.funnelActionLayout.add(funnelActions)
    },

    getFunnelActionLayout: function () {

        return this.funnelActionLayout = this.isVirtualFunnelAware()
            ? new Ext.Panel({
                xtype: 'panel',
                bodyStyle: 'background: #c3d6c6; padding: 10px;',
                layout: 'hbox',
                anchor: '100%',
                items: [
                    {
                        xtype: 'tbtext',
                        style: 'margin: 8px 5px 0 0',
                        text: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.virtual_funnel_actions') + ':',
                    },
                ]
            })
            : new Ext.form.FieldSet({
                xtype: 'fieldset',
                title: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.funnel_actions'),
                collapsible: false,
                collapsed: false,
                autoHeight: true,
                defaultType: 'textfield',
                defaults: {
                    labelWidth: 200
                },
            });
    },

    getType: function () {
        return this.type;
    },

    getId: function () {
        return this.internalId;
    },

    getWorkflowId: function () {
        return this.workflowId;
    },

    getName: function () {
        return this.channelName;
    },

    setName: function (name) {
        this.channelName = name;
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