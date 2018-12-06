pimcore.registerNS('Formbuilder.comp.conditionalLogic.form');
Formbuilder.comp.conditionalLogic.form = Class.create({

    panel: null,

    formBuilder: null,

    tabPanel: null,

    conditionsContainer: null,

    actionsContainer: null,

    sectionData: null,

    sectionId: 0,

    conditionalStore: [],

    initialize: function (sectionData, sectionId, conditionalStore, formBuilder) {
        var _ = this;
        this.formBuilder = formBuilder;
        this.sectionId = sectionId;
        this.conditionalStore = conditionalStore;

        if (sectionData) {
            this.sectionData = sectionData;
        }

        this.panel = new Ext.form.FieldContainer({
            width: '100%',
            cls: 'form_builder_conditional_section_container',
            style: 'margin-top: 10px; border: 1px solid #565d56;',
            listeners: {
                updateSectionId: function (index) {
                    _.sectionId = index;
                    _.updateIndex();
                }
            }
        });
    },

    getLayout: function () {

        this.tabPanel = new Ext.TabPanel({
            title: false,
            closable: false,
            deferredRender: false,
            forceLayout: true,
            items: [
                this.getConditionContainer(),
                this.getActionContainer()
            ]
        });

        this.panel.add(this.tabPanel);
        return this.panel;

    },

    getConditionContainer: function () {

        var _ = this;
        var conditionMenu = [];

        Ext.Array.each(this.conditionalStore.conditions, function (condition) {
            conditionMenu.push({
                iconCls: condition.icon,
                text: t(condition.name),
                handler: _.addCondition.bind(_, condition.identifier)
            });
        });

        this.conditionsContainer = new Ext.Panel({
            title: t('conditions'),
            autoScroll: true,
            forceLayout: true,
            tbar: [{
                iconCls: 'pimcore_icon_add',
                menu: conditionMenu
            }],
            border: false
        });

        return this.conditionsContainer;
    },

    getActionContainer: function () {

        var _ = this;
        var actionMenu = [];

        Ext.Array.each(this.conditionalStore.actions, function (action) {
            actionMenu.push({
                iconCls: action.icon,
                text: t(action.name),
                handler: _.addAction.bind(_, action.identifier)
            });
        });

        this.actionsContainer = new Ext.Panel({
            title: t('actions'),
            autoScroll: true,
            forceLayout: true,
            tbar: [{
                iconCls: 'pimcore_icon_add',
                menu: actionMenu
            }],
            border: false
        });

        if (this.sectionData && this.sectionData.condition && this.sectionData.condition.length > 0) {
            Ext.Array.each(this.sectionData.condition, function (condition) {
                if (condition === null) {
                    return;
                }
                this.addCondition(condition.type, condition);
            }.bind(this));
        }

        if (this.sectionData && this.sectionData.action && this.sectionData.action.length > 0) {
            Ext.Array.each(this.sectionData.action, function (action) {
                if (action === null) {
                    return;
                }
                this.addAction(action.type, action);
            }.bind(this));
        }

        return this.actionsContainer;
    },

    /**
     * add condition item
     * @param type
     * @param data
     */
    addCondition: function (type, data) {
        try {
            var configuration = Ext.Array.filter(this.conditionalStore.conditions, function (item) {
                return item.identifier === type;
            });

            if (configuration.length !== 1) {
                throw 'invalid or no configuration found';
            }

            var itemClass = new Formbuilder.comp.conditionalLogic.condition[type](
                this, data, this.sectionId, this.conditionsContainer.items.getCount(), configuration[0]
                ),
                item = itemClass.getItem();
            this.conditionsContainer.add(item);
        } catch (e) {
            console.error('condition type "' + type + '" error:', e);
        }
    },

    /**
     *
     * @param type
     * @param data
     */
    addAction: function (type, data) {
        try {

            var configuration = Ext.Array.filter(this.conditionalStore.actions, function (item) {
                return item.identifier === type;
            });

            if (configuration.length !== 1) {
                throw 'invalid or no configuration found';
            }

            var itemClass = new Formbuilder.comp.conditionalLogic.action[type](
                this, data, this.sectionId, this.actionsContainer.items.getCount(), configuration[0]
                ),
                item = itemClass.getItem();
            this.actionsContainer.add(item);
        } catch (e) {
            console.error('action type "' + type + '" error:', e);
        }
    },

    /**
     *
     * @param type
     * @param index
     */
    removeField: function (type, index) {
        //we need to re-add all elements to avoid index gaps on saving.
        if (type === 'condition') {
            this.conditionsContainer.remove(Ext.getCmp(index));
            this.updateIndex();
        } else if (type === 'action') {
            this.actionsContainer.remove(Ext.getCmp(index));
            this.updateIndex();
        }
    },

    updateIndex: function () {
        var _ = this;
        Ext.Array.each([this.conditionsContainer, this.actionsContainer], function (container) {
            container.items.each(function (component, index) {
                component.items.each(function (subComponent) {
                    subComponent.fireEvent('updateIndexName', _.sectionId, index);
                    if (subComponent.items && subComponent.items.items.length > 0) {
                        Ext.Array.each(subComponent.items.items, function (field) {
                            field.fireEvent('updateIndexName', _.sectionId, index);
                        });
                    }
                });
            });
        });
    },

    getFormFields: function (validFieldTypes) {
        return this.formBuilder.getFields(validFieldTypes ? validFieldTypes : 'field');
    },

    getFormConstraints: function () {
        return this.formBuilder.availableConstraints;
    }
});