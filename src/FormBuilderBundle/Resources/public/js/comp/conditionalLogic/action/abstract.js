pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.abstract');
Formbuilder.comp.conditionalLogic.action.abstract = Class.create({

    panel: null,

    data: null,

    sectionId: 0,

    index: 0,

    initialize: function (panel, data, sectionId, index) {
        this.panel = panel;
        this.data = data;
        this.sectionId = sectionId;
        this.index = index;
    },

    getItem: function () {
        return [];
    },

    generateFieldName: function (sectionId, index, name) {
        return 'cl.' + sectionId + '.action.' + index + '.' + name;
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
                    _.panel.removeField('action', index);
                }.bind(window, index, parent)
            }];
    },
});
