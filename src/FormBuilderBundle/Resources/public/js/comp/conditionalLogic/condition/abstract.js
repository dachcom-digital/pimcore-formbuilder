pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition.abstract');
Formbuilder.comp.conditionalLogic.condition.abstract = Class.create({

    panel: null,

    data: null,

    initialize: function (panel, data) {
        this.panel = panel;
        this.data = data;
    },

    getItem: function () {
        return [];
    },

    detectBlockIndex: function (blockElement, container) {
        var index;
        for (var s = 0; s < container.items.items.length; s++) {
            if (container.items.items[s].getId() == blockElement.getId()) {
                index = s;
                break;
            }
        }
        return index;
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
            '-',
            {
                iconCls: 'pimcore_icon_up',
                handler: function (blockId, parent) {

                    var container = _.panel.conditionsContainer;
                    var blockElement = Ext.getCmp(blockId);
                    var index = _.detectBlockIndex(blockElement, container);
                    var tmpContainer = pimcore.viewport;

                    var newIndex = index - 1;
                    if (newIndex < 0) {
                        newIndex = 0;
                    }

                    container.remove(blockElement, false);
                    tmpContainer.add(blockElement);
                    container.updateLayout();
                    tmpContainer.updateLayout();

                    tmpContainer.remove(blockElement, false);
                    container.insert(newIndex, blockElement);
                    container.updateLayout();
                    tmpContainer.updateLayout();

                    pimcore.layout.refresh();

                }.bind(window, index, parent)
            },
            {
                iconCls: 'pimcore_icon_down',
                handler: function (blockId, parent) {

                    var container = _.panel.conditionsContainer;
                    var blockElement = Ext.getCmp(blockId);
                    var index = _.detectBlockIndex(blockElement, container);
                    var tmpContainer = pimcore.viewport;

                    container.remove(blockElement, false);
                    tmpContainer.add(blockElement);
                    container.updateLayout();
                    tmpContainer.updateLayout();

                    tmpContainer.remove(blockElement, false);
                    container.insert(index + 1, blockElement);
                    container.updateLayout();
                    tmpContainer.updateLayout();

                    pimcore.layout.refresh();

                }.bind(window, index, parent)
            },
            '->',
            {
                iconCls: 'pimcore_icon_delete',
                handler: function (index, parent) {
                    _.panel.conditionsContainer.remove(Ext.getCmp(index));
                }.bind(window, index, parent)
            }
        ];
    }
});
