pimcore.registerNS('Formbuilder.extjs.components.successMessageToggleComponent');
Formbuilder.extjs.components.successMessageToggleComponent = Class.create({

    valueField: null,
    fieldPanel: null,

    fieldId: null,
    data: null,
    addDefaultLocaleField: null,
    componentConfiguration: null,
    defaultBodyStyle: null,

    initialize: function (fieldId, componentConfiguration, data, addDefaultLocaleField) {
        this.fieldId = fieldId;
        this.componentConfiguration = componentConfiguration;
        this.data = data;
        this.addDefaultLocaleField = addDefaultLocaleField === true;
        this.defaultBodyStyle = 'padding: 10px 30px 10px 30px; min-height:30px;'
    },

    setBodyStyle: function (bodyStyle) {
        this.defaultBodyStyle = bodyStyle;
    },

    getLayout: function () {

        var _ = this,
            items = [
                {
                    xtype: 'hidden',
                    name: _.componentConfiguration.onGenerateFieldName('type'),
                    value: this.componentConfiguration.identifier,
                    listeners: {
                        updateIndexName: function () {
                            var args = Array.prototype.slice.call(arguments);
                            _.componentConfiguration.onGenerateFieldName('type', args, this);
                        }
                    }
                },
                {
                    xtype: 'combo',
                    name: _.componentConfiguration.onGenerateFieldName('identifier'),
                    fieldLabel: t('form_builder_success_message_identifier'),
                    style: 'margin: 0 5px 0 0',
                    queryDelay: 0,
                    displayField: 'key',
                    valueField: 'value',
                    mode: 'local',
                    labelAlign: 'top',
                    store: new Ext.data.ArrayStore({
                        fields: ['value', 'key'],
                        data: [
                            ['string', t('form_builder_success_message_identifier_string')],
                            ['snippet', t('form_builder_success_message_identifier_snippet')],
                            ['redirect', t('form_builder_success_message_identifier_redirect')],
                            ['redirect_external', t('form_builder_success_message_identifier_redirect_external')]
                        ]
                    }),
                    editable: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    value: this.data ? this.data.identifier : null,
                    summaryDisplay: true,
                    allowBlank: false,
                    flex: 1,
                    listeners: {
                        updateIndexName: function () {
                            var args = Array.prototype.slice.call(arguments);
                            _.componentConfiguration.onGenerateFieldName('identifier', args, this);
                        },
                        change: function (field, value) {
                            this.generateValueField(value);
                        }.bind(this)
                    }
                }
            ],
            compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: 'padding-bottom:5px;',
                items: items
            });

        this.fieldPanel = new Ext.form.FormPanel({
            id: this.fieldId,
            forceLayout: true,
            style: 'margin: 10px 0 0 0',
            bodyStyle: this.defaultBodyStyle,
            tbar: this.componentConfiguration.onGenerateTopBar(),
            items: compositeField
        });

        // add initial value field
        if (this.data && this.data.identifier) {
            this.generateValueField(this.data.identifier);
        }

        return this.fieldPanel;
    },

    generateValueField: function (value) {

        if (this.valueField !== null) {
            this.fieldPanel.remove(this.valueField);
        }

        if (value === 'string') {
            this.valueField = this.generateStringValueField();
        } else if (value === 'snippet') {
            this.valueField = this.generateSnippetValueField();
        } else if (value === 'redirect') {
            this.valueField = this.generateRedirectValueField();
        } else if (value === 'redirect_external') {
            this.valueField = this.generateExternalRedirectValueField();
        }

        this.fieldPanel.add(this.valueField);
    },

    generateStringValueField: function () {

        var _ = this;

        return new Ext.form.TextField({
            name: this.componentConfiguration.onGenerateFieldName('value'),
            fieldLabel: t('form_builder_success_message_text'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: false,
            emptyText: t('form_builder_success_message_text_empty'),
            value: this.data ? (typeof this.data.value === 'string' ? this.data.value : null) : null,
            flex: 1,
            listeners: {
                updateIndexName: function () {
                    var args = Array.prototype.slice.call(arguments);
                    _.componentConfiguration.onGenerateFieldName('value', args, this);
                }
            }
        });
    },

    generateSnippetValueField: function () {
        var _ = this, fieldData, localizedField, localizedValueField;

        fieldData = this.data ? (typeof this.data.value === 'object' ? this.data.value : {}) : {};
        localizedField = new Formbuilder.extjs.types.localizedField(
            function (locale) {
                var localeValue = fieldData && fieldData.hasOwnProperty(locale) ? fieldData[locale] : null,
                    fieldConfig = {
                        label: t('form_builder_success_message_snippet'),
                        id: _.componentConfiguration.onGenerateFieldName('value'),
                        config: {
                            types: ['document'],
                            subtypes: {document: ['snippet']}
                        }
                    }, hrefElement;

                hrefElement = this.generateHrefTye(fieldConfig, localeValue, locale);
                hrefElement.on('updateIndexName', function () {
                    var args = Array.prototype.slice.call(arguments);
                    _.componentConfiguration.onGenerateFieldName('value.' + this.getHrefLocale(), args, this);
                });
                return hrefElement;
            }.bind(this), this.addDefaultLocaleField
        );

        localizedValueField = localizedField.getField();
        localizedValueField.on('updateIndexName', function () {
            var args = Array.prototype.slice.call(arguments);
            _.componentConfiguration.onGenerateFieldName('localizedValueField', args, this);
        });

        return localizedValueField;
    },

    generateRedirectValueField: function () {
        var _ = this, fieldData, localizedField, valueField, localizedValueField, flashMessageField;

        fieldData = this.data ? (typeof this.data.value === 'object' ? this.data.value : {}) : {};
        localizedField = new Formbuilder.extjs.types.localizedField(
            function (locale) {
                var localeValue = fieldData && fieldData.hasOwnProperty(locale) ? fieldData[locale] : null,
                    fieldConfig = {
                        label: t('form_builder_success_message_document'),
                        id: _.componentConfiguration.onGenerateFieldName('value'),
                        config: {
                            types: ['document'],
                            subtypes: {document: ['page']}
                        }
                    }, hrefElement;

                hrefElement = this.generateHrefTye(fieldConfig, localeValue, locale);
                hrefElement.on('updateIndexName', function () {
                    var args = Array.prototype.slice.call(arguments);
                    _.componentConfiguration.onGenerateFieldName('value.' + this.getHrefLocale(), args, this);
                });
                return hrefElement;
            }.bind(this), this.addDefaultLocaleField
        );

        localizedValueField = localizedField.getField();
        localizedValueField.on('updateIndexName', function () {
            var args = Array.prototype.slice.call(arguments);
            _.componentConfiguration.onGenerateFieldName('localizedValueField', args, this);
        });

        flashMessageField = new Ext.form.TextField({
            name: _.componentConfiguration.onGenerateFieldName('flashMessage'),
            fieldLabel: t('form_builder_success_message_flash_message_text'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: false,
            emptyText: t('form_builder_success_message_text_empty'),
            value: this.data ? this.data.flashMessage : null,
            flex: 1,
            listeners: {
                updateIndexName: function () {
                    var args = Array.prototype.slice.call(arguments);
                    _.componentConfiguration.onGenerateFieldName('value', args, this);
                }
            }
        });

        valueField = new Ext.form.FormPanel({
            forceLayout: true,
            style: '',
            bodyStyle: '',
            items: [localizedValueField, flashMessageField]
        });

        return valueField;
    },

    generateExternalRedirectValueField: function () {
        var _ = this;
        return new Ext.form.TextField({
            name: this.componentConfiguration.onGenerateFieldName('value'),
            fieldLabel: t('form_builder_success_message_external_url'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: false,
            emptyText: t('form_builder_success_message_external_url_text_empty'),
            value: this.data ? (typeof this.data.value === 'string' ? this.data.value : null) : null,
            flex: 1,
            listeners: {
                updateIndexName: function () {
                    var args = Array.prototype.slice.call(arguments);
                    _.componentConfiguration.onGenerateFieldName('value', args, this);
                }
            }
        });
    },

    generateHrefTye: function (fieldConfig, localeValue, locale) {
        var hrefFieldType = new Formbuilder.extjs.types.href(fieldConfig, localeValue, locale);
        return hrefFieldType.getHref();
    }
});
