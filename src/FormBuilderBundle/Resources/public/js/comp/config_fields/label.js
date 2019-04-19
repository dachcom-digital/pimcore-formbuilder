pimcore.registerNS('Formbuilder.comp.type.config_fields.label');
Formbuilder.comp.type.config_fields.label = Class.create(Formbuilder.comp.type.config_fields.abstract, {
    getField: function(fieldConfig, value)
    {
        return new Ext.form.Label({
            style: 'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
            text: fieldConfig.label
        });
    }
});
