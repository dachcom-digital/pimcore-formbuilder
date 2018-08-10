pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.successMessage');
Formbuilder.comp.conditionalLogic.action.successMessage = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

    valueField: null,

    fieldPanel: null,

    updateInternalPositionIndex: function (sectionId, index) {
        this.sectionId = sectionId;
        this.index = index;
    },

    getItem: function () {

        var _ = this, myId = Ext.id();

        var fieldStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'display_name'],
            data: this.panel.getFormFields().fields
        });

        var items = [
            {
                xtype: 'hidden',
                name: _.generateFieldName(this.sectionId, this.index, 'type'),
                value: this.fieldConfiguration.identifier,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        _.updateInternalPositionIndex(sectionId, index);
                        this.name = _.generateFieldName(sectionId, index, 'type');
                    }
                }
            },
            {
                xtype: 'combo',
                name: _.generateFieldName(this.sectionId, this.index, 'identifier'),
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
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'identifier');
                    },
                    change: function (field, value) {
                        this.generateValueField(value);
                    }.bind(this)
                }
            }
        ];

        var compositeField = new Ext.form.FieldContainer({
            layout: 'hbox',
            hideLabel: true,
            style: 'padding-bottom:5px;',
            items: items
        });

        this.fieldPanel = new Ext.form.FormPanel({
            id: myId,
            forceLayout: true,
            style: 'margin: 10px 0 0 0',
            bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
            tbar: this.getTopBar(myId),
            items: compositeField
        });

        // add initial value field
        if (this.data && this.data.identifier) {
            this.generateValueField(this.data.identifier);
        }

        return this.fieldPanel;
    },

    /**
     * @param value
     */
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

    /**
     * @returns {Ext.form.TextField}
     */
    generateStringValueField: function () {
        var _ = this;
        return new Ext.form.TextField({
            name: this.generateFieldName(this.sectionId, this.index, 'value'),
            fieldLabel: t('form_builder_success_message_text'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: false,
            emptyText: t('form_builder_success_message_text_empty'),
            value: this.data ? (typeof this.data.value === 'string' ? this.data.value : null) : null,
            flex: 1,
            listeners: {
                updateIndexName: function (sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value');
                }
            }
        });
    },

    /**
     * @returns {Ext.form.FieldSet}
     */
    generateSnippetValueField: function () {
        var _ = this, fieldData, localizedField, localizedValueField;

        fieldData = this.data ? (typeof this.data.value === 'object' ? this.data.value : {}) : {};

        localizedField = new Formbuilder.comp.types.localizedField(
            function (locale) {
                var localeValue = fieldData && fieldData.hasOwnProperty(locale) ? fieldData[locale] : null,
                    fieldConfig = {
                        label: t('form_builder_success_message_snippet'),
                        id: _.generateFieldName(this.sectionId, this.index, 'value'),
                        config: {
                            types: ['document'],
                            subtypes: {document: ['snippet']}
                        }
                    }, hrefField, hrefElement;

                hrefField = new Formbuilder.comp.types.href(fieldConfig, localeValue, locale);
                hrefElement = hrefField.getHref();
                hrefElement.on('updateIndexName', function (sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value.' + this.getHrefLocale());
                });
                return hrefElement;
            }.bind(this)
        );

        localizedValueField = localizedField.getField();
        localizedValueField.on('updateIndexName', function (sectionId, index) {
            var localizedTextFields = this.query('textfield');
            if (localizedTextFields.length > 0) {
                Ext.Array.each(localizedTextFields, function (field) {
                    field.fireEvent('updateIndexName', sectionId, index);
                });
            }
        });

        return localizedValueField;
    },

    /**
     * @returns {Ext.form.FormPanel}
     */
    generateRedirectValueField: function () {
        var _ = this, fieldData, localizedField, valueField, localizedValueField, flashMessageField;

        fieldData = this.data ? (typeof this.data.value === 'object' ? this.data.value : {}) : {};
        localizedField = new Formbuilder.comp.types.localizedField(
            function (locale) {
                var localeValue = fieldData && fieldData.hasOwnProperty(locale) ? fieldData[locale] : null,
                    fieldConfig = {
                        label: t('form_builder_success_message_document'),
                        id: _.generateFieldName(this.sectionId, this.index, 'value'),
                        config: {
                            types: ['document'],
                            subtypes: {document: ['page']}
                        }
                    }, hrefField, hrefElement;

                hrefField = new Formbuilder.comp.types.href(fieldConfig, localeValue, locale);
                hrefElement = hrefField.getHref();
                hrefElement.on('updateIndexName', function (sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value.' + this.getHrefLocale());
                });
                return hrefElement;
            }.bind(this)
        );

        localizedValueField = localizedField.getField();
        localizedValueField.on('updateIndexName', function (sectionId, index) {
            var localizedTextFields = this.query('textfield');
            if (localizedTextFields.length > 0) {
                Ext.Array.each(localizedTextFields, function (field) {
                    field.fireEvent('updateIndexName', sectionId, index);
                });
            }
        });

        flashMessageField = new Ext.form.TextField({
            name: _.generateFieldName(this.sectionId, this.index, 'flashMessage'),
            fieldLabel: t('form_builder_success_message_flash_message_text'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: false,
            emptyText: t('form_builder_success_message_text_empty'),
            value: this.data ? this.data.flashMessage : null,
            flex: 1,
            listeners: {
                updateIndexName: function (sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value');
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

    /**
     * @returns {Ext.form.TextField}
     */
    generateExternalRedirectValueField: function () {
        var _ = this;
        return new Ext.form.TextField({
            name: this.generateFieldName(this.sectionId, this.index, 'value'),
            fieldLabel: t('form_builder_success_message_external_url'),
            anchor: '100%',
            labelAlign: 'top',
            summaryDisplay: true,
            allowBlank: false,
            emptyText: t('form_builder_success_message_external_url_text_empty'),
            value: this.data ? (typeof this.data.value === 'string' ? this.data.value : null) : null,
            flex: 1,
            listeners: {
                updateIndexName: function (sectionId, index) {
                    this.name = _.generateFieldName(sectionId, index, 'value');
                }
            }
        });
    },

});
