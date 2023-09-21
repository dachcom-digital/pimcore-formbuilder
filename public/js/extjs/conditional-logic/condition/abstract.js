pimcore.registerNS('Formbuilder.extjs.conditionalLogic.condition');
pimcore.registerNS('Formbuilder.extjs.conditionalLogic.condition.abstract');
Formbuilder.extjs.conditionalLogic.condition.abstract = Class.create({

    panel: null,
    data: null,
    sectionId: 0,
    index: 0,
    fieldConfiguration: {},

    initialize: function (panel, data, sectionId, index, fieldConfiguration) {
        this.panel = panel;
        this.data = data;
        this.sectionId = sectionId;
        this.index = index;
        this.fieldConfiguration = fieldConfiguration;
    },

    getItem: function () {
        return [];
    },

    generateFieldName: function (sectionId, index, name) {
        return 'cl.' + sectionId + '.condition.' + index + '.' + name;
    },

    getTopBar: function (index) {
        var _ = this;
        return [
            {
                iconCls: this.fieldConfiguration.icon,
                disabled: true
            },
            {
                xtype: 'tbtext',
                text: "<b>" + t(this.fieldConfiguration.name) + "</b>"
            },
            '->',
            {
                iconCls: 'pimcore_icon_delete',
                handler: function (index, parent) {
                    _.panel.removeField('condition', index);
                }.bind(window, index, parent)
            }
        ];
    },

    checkFieldAvailability: function (fields, store, fieldName) {

        Ext.Array.each(fields, function (field, index) {
            // field is not available anymore.
            if (store.find(fieldName, field) === -1) {
                fields.splice(index, 1);
            }
        });

        return fields;
    }
});
