pimcore.registerNS('Formbuilder.extjs.form.dataInjection.expression');
Formbuilder.extjs.form.dataInjection.expression = Class.create({

    getForm: function (data) {
        return [{
            xtype: 'textfield',
            fieldLabel: 'Expression',
            name: 'expression',
            anchor: '100%',
            allowBlank: false,
            value: data !== null ? data.expression : null
        }];
    }
});