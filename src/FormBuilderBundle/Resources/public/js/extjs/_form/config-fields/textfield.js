pimcore.registerNS('Formbuilder.extjs.form.fields.textfield');
Formbuilder.extjs.form.fields.textfield = Class.create(Formbuilder.extjs.form.fields.abstract, {

    isMultiValueAware: function (fieldConfig) {

        if (!fieldConfig.hasOwnProperty('config')) {
            return false;
        }

        if (!Ext.isObject(fieldConfig.config)) {
            return false;
        }

        return fieldConfig.config.hasOwnProperty('allowDataInjector') && fieldConfig.config.allowDataInjector === true;
    },

    getMultiValueAwareValuePrefixes: function (fieldConfig) {
        return [
            fieldConfig.id + 'Injection'
        ];
    },

    getField: function (fieldConfig, value, additionalFieldValues) {

        var hasConfig = fieldConfig.hasOwnProperty('config') && Ext.isObject(fieldConfig.config),
            injectionField,
            injectionFieldName,
            injectionFieldData,
            textField,
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
                    handler: this.handleTranslatorWindow.bind(this, fieldConfig)
                }
            }
        } else if (hasConfig && fieldConfig.config.hasOwnProperty('allowDataInjector') && fieldConfig.config.allowDataInjector === true) {

            injectionFieldName = fieldConfig.id + 'Injection';
            injectionFieldData = Ext.isObject(additionalFieldValues) && additionalFieldValues.hasOwnProperty(injectionFieldName)
                ? additionalFieldValues[injectionFieldName]
                : null;

            injectionField = new Ext.form.Hidden({
                name: injectionFieldName,
                value: injectionFieldData
            });

            options.flex = 1;
            if (injectionFieldData !== null && injectionFieldData !== '') {
                options.readOnly = true;
                options.emptyText = t('form_builder_field_data_injector_active');
            }

            textField = new Ext.form.TextField(options);

            return new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                items: [
                    textField,
                    new Ext.Button({
                        iconCls: 'form_builder_icon_data_injection',
                        handler: this.handleDataInjectorWindow.bind(this, fieldConfig, injectionField, textField),
                        enableToggle: true
                    }),
                    injectionField,
                ]
            })
        }

        return new Ext.form.TextField(options);
    },

    handleTranslatorWindow: function (fieldConfig, textField) {

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
            translationArguments = ['messages', textField.getValue()];
        } else {
            // remove this if we drop pimcore support < 10.5
            translationManager = 'translationwebsitemanager';
            translationManagerClass = pimcore.settings.translation.website;
            translationArguments = [textField.getValue()];
        }

        if (pimcore.globalmanager.get(translationManager) === false) {
            pimcore.globalmanager.add(translationManager, new translationManagerClass(...translationArguments));
        } else {
            pimcore.globalmanager.get(translationManager).activate(...translationArguments);
        }

    },

    handleDataInjectorWindow: function (fieldConfig, injectionField, textField) {

        var diWindow,
            injectionFieldData = injectionField.getValue(),
            injectionFieldExtractedData = injectionFieldData === null || injectionFieldData === '' ? null : JSON.parse(injectionFieldData),
            configPanel = new Ext.form.FormPanel({
                border: false,
                hideLabel: true,
                autoScroll: false
            });

        diWindow = new Ext.Window({
            width: 600,
            height: 400,
            title: 'Data Injector',
            iconCls: 'form_builder_icon_data_injection',
            closeAction: 'destroy',
            bodyStyle: 'padding: 10px',
            plain: true,
            autoScroll: true,
            autoHeight: true,
            preventRefocus: true,
            modal: true,
            border: false,
            items: [
                {
                    xtype: 'combo',
                    fieldLabel: 'Data Injector',
                    width: 400,
                    queryDelay: 0,
                    displayField: 'label',
                    valueField: 'value',
                    mode: 'local',
                    queryMode: 'local',
                    triggerAction: 'all',
                    labelAlign: 'left',
                    value: null,
                    editable: false,
                    allowBlank: false,
                    store: new Ext.data.Store({
                        proxy: {
                            type: 'ajax',
                            url: '/admin/formbuilder/settings/get-data-injection-store',
                            fields: ['value', 'label'],
                            reader: {
                                type: 'json',
                                rootProperty: 'store'
                            },
                        },
                        listeners: {
                            load: function (store, records) {

                                store.insert(0, new Ext.data.Record({
                                    value: null,
                                    label: t('form_builder_field_no_data_injector_available')
                                }));

                                diWindow.query('combo')[0].setValue(injectionFieldExtractedData !== null ? injectionFieldExtractedData.injector : null);
                            }.bind(this),
                        }
                    }),
                    listeners: {
                        render: function (combo) {
                            combo.getStore().load();
                        }.bind(this),
                        change: function (combo, value) {

                            var injectorClass,
                                rec = combo.store.findRecord('value', value);

                            configPanel.removeAll();

                            if (value === null) {
                                return;
                            }

                            if (rec.get('description') !== null) {
                                configPanel.add({
                                    hideLabel: true,
                                    xtype: 'displayfield',
                                    style: 'display:block; font-weight: 300;',
                                    value: rec.get('description')
                                })
                            }

                            injectorClass = new Formbuilder.extjs.form.dataInjection['expression'];
                            configPanel.add(injectorClass.getForm(injectionFieldExtractedData !== null ? injectionFieldExtractedData.config : null));
                        }
                    }
                },
                configPanel
            ],
            buttons: [
                {
                    text: t('save'),
                    iconCls: 'pimcore_icon_save',
                    handler: function () {
                        var value,
                            dataInjectorSelector = diWindow.query('combo')[0];

                        value = dataInjectorSelector.getValue() === null ? null : JSON.stringify({
                            injector: diWindow.query('combo')[0].getValue(),
                            config: configPanel.getForm().getValues()
                        });

                        if (!configPanel.getForm().isValid()) {
                            return;
                        }

                        injectionField.setValue(value);

                        if (value !== null) {
                            textField.setValue(null);
                        }

                        textField.setEmptyText(value !== null ? t('form_builder_field_data_injector_active') : null);
                        textField.setReadOnly(value !== null);

                        diWindow.close();
                    }
                }
            ]
        });

        diWindow.show();
    }
});
