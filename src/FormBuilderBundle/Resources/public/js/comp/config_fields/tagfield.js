pimcore.registerNS('Formbuilder.comp.type.config_fields.tagfield');
Formbuilder.comp.type.config_fields.tagfield = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function(fieldConfig, value)
    {
        var hasStore = fieldConfig.config && Ext.isArray(fieldConfig.config.store),
            tagStore = new Ext.data.ArrayStore({
                fields: ['index', 'name'],
                data: hasStore ? fieldConfig.config.store : []
            });

        return new Ext.form.field.Tag({
            name: fieldConfig.id,
            fieldLabel: fieldConfig.label,
            queryDelay: 0,
            store: tagStore,
            value: value,
            createNewOnEnter: !hasStore,
            createNewOnBlur: !hasStore,
            filterPickList: hasStore,
            mode: 'local',
            displayField: 'name',
            valueField: 'index',
            hideTrigger: true,
            editable: !hasStore,
            anchor: '100%'
        });
    }
});
