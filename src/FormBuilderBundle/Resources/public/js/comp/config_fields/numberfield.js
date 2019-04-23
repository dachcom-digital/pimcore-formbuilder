pimcore.registerNS('Formbuilder.comp.type.config_fields.numberfield');
Formbuilder.comp.type.config_fields.numberfield = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function(fieldConfig, value)
    {
        return new Ext.form.field.Number({
            name: fieldConfig.id,
            fieldLabel: fieldConfig.label,
            allowDecimals: false,
            anchor: '100%',
            value: value
        });
    }
});
