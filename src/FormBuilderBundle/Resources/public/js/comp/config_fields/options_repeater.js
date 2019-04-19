pimcore.registerNS('Formbuilder.comp.type.config_fields.options_repeater');
Formbuilder.comp.type.config_fields.options_repeater = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function(fieldConfig, value)
    {
        var keyValueRepeater = new Formbuilder.comp.types.keyValueRepeater(
            fieldConfig.id,
            fieldConfig.label,
            value,
            fieldConfig.config.options,
            false
        );

        return keyValueRepeater.getRepeater();
    }
});
