pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.mailBehaviour');
Formbuilder.comp.conditionalLogic.action.mailBehaviour = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

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
                fieldLabel: t('form_builder_mail_behaviour_identifier'),
                style: 'margin: 0 5px 0 0',
                queryDelay: 0,
                displayField: 'key',
                valueField: 'value',
                mode: 'local',
                labelAlign: 'top',
                store: new Ext.data.ArrayStore({
                    fields: ['value', 'key'],
                    data: [
                        ['recipient', t('form_builder_mail_behaviour_identifier_recipient')],
                        ['mailTemplate', t('form_builder_mail_behaviour_identifier_mail_template')]
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
            },
            {
                xtype: 'combo',
                name: _.generateFieldName(this.sectionId, this.index, 'mailType'),
                fieldLabel: t('form_builder_mail_behaviour_mail_type'),
                style: 'margin: 0 5px 0 0',
                queryDelay: 0,
                displayField: 'key',
                valueField: 'value',
                mode: 'local',
                labelAlign: 'top',
                store: new Ext.data.ArrayStore({
                    fields: ['value', 'key'],
                    data: [
                        ['main', t('form_builder_mail_behaviour_mail_type_main')],
                        ['copy', t('form_builder_mail_behaviour_mail_type_copy')]
                    ]
                }),
                editable: true,
                triggerAction: 'all',
                anchor: '100%',
                value: this.data && this.data.mailType ? this.data.mailType : 'main',
                summaryDisplay: true,
                allowBlank: false,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'mailType');
                    }
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

        if (value === 'recipient') {
            this.valueField = new Ext.form.TextField({
                name: _.generateFieldName(this.sectionId, this.index, 'value'),
                vtype: 'email',
                cls: 'form_builder_mail_behaviour_value_field',
                fieldLabel: t('form_builder_mail_behaviour_mail_address'),
                anchor: '100%',
                labelAlign: 'top',
                summaryDisplay: true,
                allowBlank: false,
                value: this.data ? (typeof this.data.value === 'string' ? this.data.value : null) : null,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'value');
                    }
                }
            });
        } else if (value === 'mailTemplate') {
            var fieldData = this.data ? (typeof this.data.value === 'object' ? this.data.value : {}) : {};
            var localizedField = new Formbuilder.comp.types.localizedField(
                function (locale) {
                    var localeValue = fieldData && fieldData.hasOwnProperty(locale) ? fieldData[locale] : null,
                        fieldConfig = {
                            label: t('form_builder_mail_behaviour_mail_path'),
                            id: _.generateFieldName(this.sectionId, this.index, 'value'),
                            config: {
                                types: ['document'],
                                subtypes: {document: ['email']}
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
