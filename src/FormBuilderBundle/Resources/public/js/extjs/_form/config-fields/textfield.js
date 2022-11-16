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

                        var
                            user = pimcore.globalmanager.get('user'),
                            translationManager,
                            translationManagerClass,
                            translationArguments;

                        if (user && !user.isAllowed('translations')) {
                            alert(t('access_denied'));
                            return;
                        }

                        if (typeof pimcore.settings.translation.website === 'undefined') {
                            translationManager = 'translationdomainmanager';
                            translationManagerClass = pimcore.settings.translation.domain;
                            translationArguments = ['messages', this.getValue()];
                        } else {
                            // remove this if we drop pimcore support < 10.5
                            translationManager = 'translationwebsitemanager';
                            translationManagerClass = pimcore.settings.translation.website;
                            translationArguments = [this.getValue()];
                        }

                        if (pimcore.globalmanager.get(translationManager) === false) {
                            pimcore.globalmanager.add(translationManager, new translationManagerClass(...translationArguments));
                        } else {
                            pimcore.globalmanager.get(translationManager).activate(...translationArguments);
                        }
                    }
                }
            }
        }

        return new Ext.form.TextField(options);
    }
});
