pimcore.registerNS('Formbuilder.comp.type.config_fields.href');
Formbuilder.comp.type.config_fields.href = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function(fieldConfig, value)
    {
         var fieldData = value,
            localizedField = new Formbuilder.comp.types.localizedField(
                function (locale) {
                    var localeValue = fieldData && fieldData.hasOwnProperty(locale) ? fieldData[locale] : null,
                        field;

                    field = new Formbuilder.comp.types.href(fieldConfig, localeValue, locale);

                    return field.getHref();
                }
            );

        return localizedField.getField();
    }
});
