pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.abstract');
Formbuilder.comp.conditionalLogic.action.abstract = Class.create({

    panel: null,

    data: null,

    initialize: function (panel, data) {
        this.panel = panel;
        this.data = data;
    },

    getItem: function () {
        return [];
    },

    getTopBar: function (name, index, parent, data, iconCls) {
        var _ = this;
        return [
            {
                iconCls: iconCls,
                disabled: true
            },
            {
                xtype: 'tbtext',
                text: "<b>" + name + "</b>"
            },
            '->',
            {
                iconCls: 'pimcore_icon_delete',
                handler: function (index, parent) {
                    _.panel.actionsContainer.remove(Ext.getCmp(index));
                }.bind(window, index, parent)
            }];
    },
});
