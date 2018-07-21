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
                        ['redirect', t('form_builder_success_message_identifier_redirect')]
                    ]
                }),
                editable: true,
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
            type: 'combo',
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

    generateValueField: function (value) {

        var _ = this;

        if (this.valueField !== null) {
            this.fieldPanel.remove(this.valueField);
        }

        if (value === 'string') {
            this.valueField = new Ext.form.TextField({
                name: _.generateFieldName(this.sectionId, this.index, 'value'),
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
        } else if (value === 'snippet') {
            var fieldData = this.data ? (typeof this.data.value === 'object' ? this.data.value : {}) : {};
            var localizedField = new Formbuilder.comp.types.localizedField(
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

            localizedElementField = localizedField.getField();
            localizedElementField.on('updateIndexName', function (sectionId, index) {
                var localizedTextFields = this.query('textfield');
                if (localizedTextFields.length > 0) {
                    Ext.Array.each(localizedTextFields, function (field) {
                        field.fireEvent('updateIndexName', sectionId, index);
                    });
                }
            });

            this.valueField = localizedElementField;

        } else if (value === 'redirect') {
            var fieldData = this.data ? (typeof this.data.value === 'object' ? this.data.value : {}) : {};
            var localizedField = new Formbuilder.comp.types.localizedField(
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

            localizedElementField = localizedField.getField();
            localizedElementField.on('updateIndexName', function (sectionId, index) {
                var localizedTextFields = this.query('textfield');
                if (localizedTextFields.length > 0) {
                    Ext.Array.each(localizedTextFields, function (field) {
                        field.fireEvent('updateIndexName', sectionId, index);
                    });
                }
            });

            this.valueField = localizedElementField;
        }

        this.fieldPanel.add(this.valueField);
    }
});
