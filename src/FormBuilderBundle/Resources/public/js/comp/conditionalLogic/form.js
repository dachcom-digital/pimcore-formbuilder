pimcore.registerNS('Formbuilder.comp.conditionalLogic.form');
Formbuilder.comp.conditionalLogic.form = Class.create({

    panel: null,

    tabPanel: null,

    conditionsContainer: null,

    actionsContainer: null,

    currentIndex: 0,

    initialize: function (formData) {
        this.currentIndex = 0;
        this.panel = new Ext.form.FieldContainer({
            width: '100%',
            style: 'margin-top: 10px; border: 1px solid #565d56;',
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
                name: 'field',
                method: 'field',
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

        return this.actionsContainer;
    },

    /**
     * add condition item
     * @param type
     * @param data
     */
    addCondition: function (type, data) {
        var itemClass = new Formbuilder.comp.conditionalLogic.condition[type](this, data),
            item = itemClass.getItem();
        this.conditionsContainer.add(item);
        item.updateLayout();
        this.conditionsContainer.updateLayout();
        this.currentIndex++;
    },

    addAction: function (type, data) {
        var itemClass = new Formbuilder.comp.conditionalLogic.action[type](this, data),
            item = itemClass.getItem();
        this.actionsContainer.add(item);
        item.updateLayout();
        this.actionsContainer.updateLayout();
    },
});