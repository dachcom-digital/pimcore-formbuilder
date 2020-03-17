pimcore.registerNS('Formbuilder.extjs.form.fields.textfield');
Formbuilder.extjs.form.fields.textfield = Class.create(Formbuilder.extjs.form.fields.abstract, {
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
