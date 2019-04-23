pimcore.registerNS('Formbuilder.comp.type.config_fields.select');
Formbuilder.comp.type.config_fields.select = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function(fieldConfig, value)
    {
        var selectStore;

        if (fieldConfig.config.store_url) {
            selectStore = new Ext.data.JsonStore({
                autoLoad: true,
                fields: ['label', 'value'],
                proxy: {
                    type: 'ajax',
                    url: fieldConfig.config.store_url,
                    reader: {
                        type: 'json'
                    }
                }
            });
        } else {
            selectStore = new Ext.data.ArrayStore({
                fields: ['label', 'value'],
                data: fieldConfig.config.options
            });
        }

        return new Ext.form.ComboBox({
            fieldLabel: fieldConfig.label,
            name: fieldConfig.id,
            value: value,
            queryDelay: 0,
            displayField: 'label',
            valueField: 'value',
            mode: 'local',
            store: selectStore,
            editable: false,
            triggerAction: 'all',
            anchor: '100%',
            allowBlank: false
        });
    }
});
