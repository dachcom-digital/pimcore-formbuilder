pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition.abstract');
Formbuilder.comp.conditionalLogic.condition.abstract = Class.create({

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

    generateFieldName: function (index, name) {
        return 'cl.' + this.sectionId + '.condition.' + index + '.' + name;
    },

    getTopBar: function (name, index, iconCls) {

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
                    _.panel.removeField('condition', index);
                }.bind(window, index, parent)
            }
        ];
    }
});
