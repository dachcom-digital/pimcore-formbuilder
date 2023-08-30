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
    funnelActionDispatcherDataClasses: [],

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
        this.funnelActionDispatcherDataClasses = [];

        if (this.channelName === null) {
            uuidGenerator = new Ext.data.identifier.Uuid();
            this.channelName = uuidGenerator.generate();
        }

        this.internalId = 'output_channel_' + this.getType() + '_' + Ext.id();
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

    funnelActionsValid: function () {

        var valid = true;

        if (this.funnelActionDispatcherDataClasses.length === 0) {
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

        if (this.funnelActionDispatcherDataClasses.length === 0) {
            return null;
        }

        Ext.Array.each(this.funnelActionDispatcherDataClasses, function (funnelActionDispatcherDataClass) {
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

    populateFunnelActions: function (funnelActionDefinitions, dynamicFunnelActionAware, clearActionLayout) {

        if (this.funnelActionLayout === null) {
            return;
        }

        if (clearActionLayout === true) {
            this.funnelActionLayout.removeAll();
        }

        if (dynamicFunnelActionAware === true) {
            this.addDynamicFunnelActionHandler();
            this.addDynamicFunnelActions();

            return;
        }

        this.addPreDefinedFunnelActions(funnelActionDefinitions);
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
                }
            });
    },

    refreshDynamicFunnelActionPlaceholder: function () {

        if (this.funnelActionLayout === null) {
            return;
        }

        var toolbarLabels = this.funnelActionLayout.query('toolbar displayfield'),
            toolbarLabel,
            placeholders = [],
            renderedPlaceHolders = '';

        if (toolbarLabels.length === 0) {
            return;
        }

        toolbarLabel = toolbarLabels[0];

        Ext.Array.each(this.funnelActionDispatcherDataClasses, function (funnelActionDispatcherDataClasses) {
            var style = 'background: #1d1d1d; border-radius: 3px; display: inline-block; padding: 2px 4px; color: white; margin-bottom: 3px;';
            placeholders.push('<span style="' + style + '">{{ form_widget(form.' + (funnelActionDispatcherDataClasses.funnelActionDefinition.name) + ') }}</span>');
        }.bind(this));

        if (placeholders.length > 0) {
            renderedPlaceHolders = placeholders.join(', ');
        }

        toolbarLabel.setValue(t('form_builder.output_workflow.output_workflow_channel.funnel_layer.dynamic_funnel_action_description') + ': ' + renderedPlaceHolders);
    },

    addDynamicFunnelActionHandler: function () {

        this.funnelActionLayout.add({
            xtype: 'toolbar',
            items: [
                {
                    flex: 3,
                    fieldLabel: false,
                    xtype: 'displayfield',
                    style: 'display:block; margin-bottom:10px; font-weight: 300;',
                    value: t('form_builder.output_workflow.output_workflow_channel.funnel_layer.dynamic_funnel_action_description')
                },
                '->',
                {
                    flex: 1,
                    xtype: 'button',
                    text: t('add'),
                    scale: 'small',
                    iconCls: 'pimcore_icon_add',
                    handler: function () {
                        Ext.MessageBox.prompt(
                            t('form_builder.output_workflow.output_workflow_channel.funnel_layer.create_funnel_action_button'),
                            t('form_builder.output_workflow.output_workflow_channel.funnel_layer.create_funnel_action_name'),
                            function (button, newFunnelActionLabel) {

                                var actionButton,
                                    funnelActionDispatcherDataClass,
                                    funnelActionDefinition,
                                    newFunnelActionName,
                                    valid = true;

                                if (newFunnelActionLabel === null || newFunnelActionLabel === '') {
                                    return;
                                }

                                newFunnelActionName = this.generateSaveActionName(newFunnelActionLabel);

                                Ext.Array.each(this.funnelActionDispatcherDataClasses, function (funnelActionDispatcherDataClasses) {
                                    if (newFunnelActionName === funnelActionDispatcherDataClasses.funnelActionDefinition.name) {
                                        valid = false;
                                        return false;
                                    }
                                }.bind(this));

                                if (valid === false) {
                                    Ext.Msg.alert(t('error'), t('form_builder.output_workflow.output_workflow_channel.funnel_layer.funnel_action_name_already_given'));

                                    return;
                                }

                                funnelActionDefinition = {
                                    name: newFunnelActionName,
                                    label: newFunnelActionLabel,
                                }

                                funnelActionDispatcherDataClass = this.buildFunnelActionDispatcher(funnelActionDefinition, null, true);
                                actionButton = funnelActionDispatcherDataClass.buildActionElement();

                                this.funnelActionLayout.add(actionButton);
                                this.funnelActionDispatcherDataClasses.push(funnelActionDispatcherDataClass);

                                this.addDynamicFunnelActionContextMenu(funnelActionDispatcherDataClass, actionButton);
                                this.refreshDynamicFunnelActionPlaceholder();

                            }.bind(this),
                            null, null, ''
                        );
                    }.bind(this)
                }
            ]
        });
    },

    addPreDefinedFunnelActions: function (funnelActionDefinitions) {

        Ext.Array.each(funnelActionDefinitions, function (funnelActionDefinition) {

            var actionButton,
                funnelActionDispatcherDataClass,
                funnelActionConfig = null;

            if (this.getFunnelActions() !== null) {
                Ext.Array.each(this.getFunnelActions(), function (funnelAction) {
                    if (funnelAction.triggerName === funnelActionDefinition.name) {
                        funnelActionConfig = funnelAction;
                        return false;
                    }
                });
            }

            funnelActionDispatcherDataClass = this.buildFunnelActionDispatcher(funnelActionDefinition, funnelActionConfig, false);
            actionButton = funnelActionDispatcherDataClass.buildActionElement();

            this.addDynamicFunnelActionContextMenu(funnelActionDispatcherDataClass, actionButton);

            this.funnelActionLayout.add(actionButton);
            this.funnelActionDispatcherDataClasses.push(funnelActionDispatcherDataClass);

        }.bind(this))
    },

    addDynamicFunnelActions: function () {

        if (this.getFunnelActions() === null) {
            return;
        }

        Ext.Array.each(this.getFunnelActions(), function (funnelActionConfig) {

            var funnelActionDispatcherDataClass,
                funnelActionDefinition;

            funnelActionDefinition = {
                name: funnelActionConfig.triggerName,
                label: funnelActionConfig.label
            };

            funnelActionDispatcherDataClass = this.buildFunnelActionDispatcher(funnelActionDefinition, funnelActionConfig, true);

            this.funnelActionLayout.add(funnelActionDispatcherDataClass.buildActionElement());
            this.funnelActionDispatcherDataClasses.push(funnelActionDispatcherDataClass);

        }.bind(this));

        this.refreshDynamicFunnelActionPlaceholder();
    },

    addDynamicFunnelActionContextMenu: function (funnelActionDispatcherDataClass, actionButton) {

        actionButton.on('removeFunnelActionButton', function (funnelActionDispatcher) {

            if (this.funnelActionDispatcherDataClasses.length === 0) {
                return;
            }

            Ext.Array.each(this.funnelActionDispatcherDataClasses, function (funnelActionDispatcherDataClasses) {
                if (funnelActionDispatcher.funnelActionDefinition.name === funnelActionDispatcherDataClasses.funnelActionDefinition.name) {
                    Ext.Array.remove(this.funnelActionDispatcherDataClasses, funnelActionDispatcherDataClasses);
                }
            }.bind(this));

            this.refreshDynamicFunnelActionPlaceholder();

        }.bind(this));
    },

    buildFunnelActionDispatcher: function (funnelActionDefinition, funnelActionConfig, dynamicFunnelActionAware) {

        return new Formbuilder.extjs.formPanel.outputWorkflow.channel.funnelActionDispatcher(
            this.workflowId,
            this.channelName,
            funnelActionDefinition,
            dynamicFunnelActionAware,
            funnelActionConfig
        );
    },

    generateSaveActionName: function (value) {
        return value
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '_')
            .replace(/[^\w-]+/g, '')
            .replace(/--+/g, '_');
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