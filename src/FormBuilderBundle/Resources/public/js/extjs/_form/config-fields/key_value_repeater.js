pimcore.registerNS('Formbuilder.extjs.form.fields.key_value_repeater');
Formbuilder.extjs.form.fields.key_value_repeater = Class.create(Formbuilder.extjs.form.fields.abstract, {
    getField: function(fieldConfig, value)
    {
        var keyValueRepeater = new Formbuilder.extjs.types.keyValueRepeater(
            fieldConfig.id,
            fieldConfig.label,
            value
        );

        return keyValueRepeater.getRepeater();
    }
});
