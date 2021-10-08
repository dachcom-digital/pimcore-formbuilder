pimcore.registerNS('Formbuilder.extjs.form.fields.textfield');
Formbuilder.extjs.form.fields.textfield = Class.create(Formbuilder.extjs.form.fields.abstract, {
    getField: function (fieldConfig, value) {

        var hasConfig = fieldConfig.hasOwnProperty('config') && Ext.isObject(fieldConfig.config),
            options = {
                fieldLabel: fieldConfig.label,
                anchor: '100%',
                allowBlank: true,
                enableKeyEvents: true,
                name: fieldConfig.id,
                value: hasConfig && fieldConfig.config.hasOwnProperty('data') ? fieldConfig.config.data : value,
                disabled: hasConfig && fieldConfig.config.hasOwnProperty('disabled') ? (fieldConfig.config.disabled === true) : false
            };

        if (hasConfig && fieldConfig.config.hasOwnProperty('maxLength')) {
            options.maxLength = fieldConfig.config.maxLength;
            options.enforceMaxLength = true;
        }

        if (hasConfig && fieldConfig.config.hasOwnProperty('translatable') && fieldConfig.config.translatable === true) {
            options.inputAttrTpl = ' data-qwidth="250" data-qalign="br-r?" data-qtrackMouse="false" data-qtip="' + t('form_builder_type_field_base.translatable_field') + '"';
            options.triggers = {
                translatable: {
                    cls: 'pimcore_icon_language',
                    handler: function () {

                        var user = pimcore.globalmanager.get('user');

                        if (user && !user.isAllowed('translations')) {
                            alert(t('access_denied'));
                            return;
                        }

                        if (pimcore.globalmanager.get('translationwebsitemanager') === false) {
                            pimcore.globalmanager.add('translationwebsitemanager', new pimcore.settings.translation.website(this.getValue()));
                        } else {
                            pimcore.globalmanager.get('translationwebsitemanager').activate(this.getValue());
                        }
                    }
                }
            }
        }

        return new Ext.form.TextField(options);
    }
});
