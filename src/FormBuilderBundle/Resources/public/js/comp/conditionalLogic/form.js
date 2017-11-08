pimcore.registerNS('Formbuilder.comp.conditionalLogic.form');
Formbuilder.comp.conditionalLogic.form = Class.create({

    panel: null,

    formBuilder: null,

    tabPanel: null,

    conditionsContainer: null,

    actionsContainer: null,

    sectionData: null,

    sectionId: 0,

    initialize: function (sectionData, sectionId, formBuilder) {
        var _ = this;
        this.formBuilder = formBuilder;
        this.sectionId = sectionId;

        if (sectionData) {
            this.sectionData = sectionData;
        }

        this.panel = new Ext.form.FieldContainer({
            width: '100%',
            style: 'margin-top: 10px; border: 1px solid #565d56;',
            listeners: {
                updateSectionId: function(index) {
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
        var conditionMenu = []
        conditions = [
            {
                name: 'Field Value',
                method: 'value',
                icon: 'form_builder_icon_text',
            }
        ];

        Ext.each(conditions, function (condition) {
            conditionMenu.push({
                iconCls: condition.icon,
                text: condition.name,
                handler: _.addCondition.bind(_, condition.method)
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
        var actionMenu = []
        conditions = [
            {
                name: 'show / hide element',
                method: 'toggle',
                icon: 'form_builder_icon_text',
            },
            {
                name: 'change validation',
                method: 'changeConstraints',
                icon: 'form_builder_icon_text',
            },
            {
                name: 'change value',
                method: 'value',
                icon: 'form_builder_icon_text',
            },
            {
                name: 'trigger event',
                method: 'event',
                icon: 'form_builder_icon_text',
            },
            {
                name: 'change class',
                method: 'class',
                icon: 'form_builder_icon_text',
            }
        ];

        Ext.each(conditions, function (action) {
            actionMenu.push({
                iconCls: action.icon,
                text: action.name,
                handler: _.addAction.bind(_, action.method)
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

        if(this.sectionData && this.sectionData.condition && this.sectionData.condition.length > 0) {
            Ext.Array.each(this.sectionData.condition, function (condition) {
                if(condition === null) {
                    return;
                }
                this.addCondition(condition.type, condition);
            }.bind(this));
        }

        if(this.sectionData && this.sectionData.action && this.sectionData.action.length > 0) {
            Ext.Array.each(this.sectionData.action, function (action) {
                if(action === null) {
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
        var itemClass = new Formbuilder.comp.conditionalLogic.condition[type](this, data, this.sectionId, this.conditionsContainer.items.getCount()),
            item = itemClass.getItem();
        this.conditionsContainer.add(item);
        item.updateLayout();
        this.conditionsContainer.updateLayout();
    },

    /**
     *
     * @param type
     * @param data
     */
    addAction: function (type, data) {
        var itemClass = new Formbuilder.comp.conditionalLogic.action[type](this, data, this.sectionId, this.actionsContainer.items.getCount()),
            item = itemClass.getItem();
        this.actionsContainer.add(item);
        item.updateLayout();
        this.actionsContainer.updateLayout();
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

    updateIndex: function() {
        var _ = this;
        this.conditionsContainer.items.each(function(component, index) {
            component.items.each(function(condition){
                if(condition.items.items.length > 0) {
                    Ext.Array.each(condition.items.items, function (field) {
                        field.fireEvent('updateIndexName', _.sectionId, index);
                    });
                }
            });
        });
        this.actionsContainer.items.each(function(component, index) {
            component.items.each(function(action){
                if(action.items.items.length > 0) {
                    Ext.Array.each(action.items.items, function (field) {
                        field.fireEvent('updateIndexName', _.sectionId, index);
                    });
                }
            });
        });
    },

    /**
     *
     */
    getFormFields: function () {
        return this.formBuilder.getData();
    }
});