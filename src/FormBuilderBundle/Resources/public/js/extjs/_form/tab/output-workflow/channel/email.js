pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.email');
Formbuilder.extjs.formPanel.outputWorkflow.channel.email = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel, {

    panel: null,

    getLayout: function () {

        var items = [],
            mailTemplateField = this.generateLocalizedFieldBlock(function (locale) {
                return this.getConfigFields(locale);
            }.bind(this));

        items.push(mailTemplateField);

        this.panel = new Ext.form.FormPanel({
            title: false,
            defaults: {
                labelWidth: 200
            },
            items: items
        });

        return this.panel;
    },

    getConfigFields: function (locale) {

        var localeData = this.data !== null && this.data.hasOwnProperty(locale) ? this.data[locale] : null;

        var hrefValue = localeData !== null ? localeData['mailTemplate'] : null,
            fieldConfig = {
                label: t('form_builder.output_workflow.output_workflow_channel.email.template'),
                id: locale + '.mailTemplate',
                config: {
                    types: ['document'],
                    subtypes: {document: ['email']}
                }
            }, hrefField = new Formbuilder.extjs.types.href(fieldConfig, hrefValue, null);

        return [
            hrefField.getHref(),
            {
                xtype: 'tagfield',
                value: localeData !== null ? localeData['ignoreFields'] : null,
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.email.ignore_fields'),
                name: locale + '.ignoreFields',
                queryDelay: 0,
                displayField: 'name',
                valueField: 'index',
                mode: 'local',
                labelAlign: 'top',
                store: new Ext.data.ArrayStore({
                    fields: ['index', 'name'],
                    data: []
                }),
                selectOnFocus: false,
                createNewOnBlur: true,
                createNewOnEnter: true,
                filterPickList: false,
                editable: true,
                hideTrigger: true,
                allowBlank: false,
                anchor: '100%',
                flex: 1,
                autoEl: {
                    tag: 'div',
                    'data-qtip': t('form_builder.output_workflow.output_workflow_channel.email.ignore_fields_help')
                }
            },
            {
                xtype: 'checkbox',
                value: localeData !== null ? localeData['forcePlainText'] : null,
                inputValue: true,
                uncheckedValue: false,
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.email.force_plain_text'),
                name: locale + '.forcePlainText',
                autoEl: {
                    tag: 'div',
                    'data-qtip': t('form_builder.output_workflow.output_workflow_channel.email.force_plain_text_help')
                }
            },
            {
                xtype: 'checkbox',
                inputValue: true,
                uncheckedValue: false,
                value: localeData !== null ? localeData['disableDefaultMailBody'] : null,
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.email.disable_default_mail_body'),
                name: locale + '.disableDefaultMailBody',
                autoEl: {
                    tag: 'div',
                    'data-qtip': t('form_builder.output_workflow.output_workflow_channel.email.disable_default_mail_body_help'),
                }
            }
        ]
    },

    isValid: function () {
        return true;
    },

    getValues: function () {
        return this.panel.form.getValues();
    }
});