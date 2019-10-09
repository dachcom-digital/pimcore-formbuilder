pimcore.registerNS('Formbuilder.comp.type.config_fields.checkbox');
Formbuilder.comp.type.config_fields.checkbox = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function (fieldConfig, value) {
        return new Ext.form.Checkbox({
            fieldLabel: fieldConfig.label,
            name: fieldConfig.id,
            checked: fieldConfig.config !== null && fieldConfig.config !== undefined && fieldConfig.config.hasOwnProperty('checked') ? fieldConfig.config.checked : false,
            uncheckedValue: false,
            inputValue: true,
            value: value
        });
    }
});
