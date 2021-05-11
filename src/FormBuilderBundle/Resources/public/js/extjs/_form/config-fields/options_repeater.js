pimcore.registerNS('Formbuilder.extjs.form.fields.options_repeater');
Formbuilder.extjs.form.fields.options_repeater = Class.create(Formbuilder.extjs.form.fields.abstract, {
    getField: function (fieldConfig, value) {
        var keyValueRepeater = new Formbuilder.extjs.types.keyValueRepeater(
            fieldConfig.id,
            fieldConfig.label,
            value,
            fieldConfig.config.options,
            false,
            false
        );

        return keyValueRepeater.getRepeater();
    }
});
