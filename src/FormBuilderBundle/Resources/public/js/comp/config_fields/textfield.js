pimcore.registerNS('Formbuilder.comp.type.config_fields.textfield');
Formbuilder.comp.type.config_fields.textfield = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function(fieldConfig, value)
    {
        return new Ext.form.TextField({
            fieldLabel: fieldConfig.label,
            name: fieldConfig.id,
            value: fieldConfig.config && fieldConfig.config.data ? fieldConfig.config.data : value,
            allowBlank: true,
            anchor: '100%',
            enableKeyEvents: true,
            disabled: fieldConfig.config ? (fieldConfig.config.disabled === true) : false
        });
    }
});
