// legacy
pimcore.registerNS('Formbuilder.comp.type.config_fields.abstract');
pimcore.registerNS('Formbuilder.extjs.form.fields.abstract');

Formbuilder.extjs.form.fields.abstract = Class.create({
    isMultiValueAware: function (fieldConfig) {
        return false;
    },
    getMultiValueAwareValuePrefixes: function (fieldConfig) {
        return [];
    },
    getField: function (fieldConfig, value) {
        return null;
    }
});

// legacy
Formbuilder.comp.type.config_fields.abstract = Class.create(Formbuilder.extjs.form.fields.abstract, {});