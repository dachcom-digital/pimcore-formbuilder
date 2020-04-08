pimcore.registerNS('Formbuilder.extjs.form.fields.href');
Formbuilder.extjs.form.fields.href = Class.create(Formbuilder.extjs.form.fields.abstract, {
    getField: function(fieldConfig, value)
    {
         var fieldData = value,
            localizedField = new Formbuilder.extjs.types.localizedField(
                function (locale) {
                    var localeValue = fieldData && fieldData.hasOwnProperty(locale) ? fieldData[locale] : null,
                        field;

                    field = new Formbuilder.extjs.types.href(fieldConfig, localeValue, locale);

                    return field.getHref();
                }
            );

        return localizedField.getField();
    }
});
