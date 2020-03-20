pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.email');
Formbuilder.extjs.formPanel.outputWorkflow.channel.email = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel, {

    panel: null,
    mailTemplateData: {},

    getLayout: function () {

        this.mailTemplateData = {};

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

        var localeData = this.data !== null && this.data.hasOwnProperty(locale) ? this.data[locale] : null,
            hasMailLayoutData = localeData !== null && localeData.hasOwnProperty('mailLayoutData') && localeData.mailLayoutData !== null;

        if (localeData !== null && hasMailLayoutData === true) {
            this.mailTemplateData[locale] = localeData.mailLayoutData;
        }

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
                allowBlank: true,
                anchor: '100%',
                flex: 1,
                autoEl: {
                    tag: 'div',
                    'data-qtip': t('form_builder.output_workflow.output_workflow_channel.email.ignore_fields_help')
                }
            },
            {
                xtype: 'checkbox',
                value: localeData !== null ? localeData['allowAttachments'] : null,
                inputValue: true,
                uncheckedValue: false,
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.email.allow_attachments'),
                name: locale + '.allowAttachments',
                autoEl: {
                    tag: 'div',
                    'data-qtip': t('form_builder.output_workflow.output_workflow_channel.email.allow_attachments_help')
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
            },
            {
                xtype: 'panel',
                layout: 'hbox',
                anchor: '100%',
                cls: 'form_builder_channel_mail_editor_panel_' + locale,
                width: 350,
                hideLabel: true,
                autoHeight: true,
                items: [
                    {
                        xtype: 'button',
                        iconCls: 'pimcore_icon_mail_editor',
                        text: t('form_builder.mail_editor.open_editor'),
                        style: 'background: #404f56; border-color: transparent;',
                        handler: this.showMailEditor.bind(this, locale)
                    },
                    {
                        xtype: 'button',
                        disable: true,
                        cls: 'form_builder_cme_status_button',
                        style: 'background: ' + (hasMailLayoutData ? '#3e943e' : '#7f8a7f') + '; border-color: transparent; cursor: default !important;',
                        text: t('form_builder.output_workflow.output_workflow_channel.email.mail_editor.' + (hasMailLayoutData ? 'status_enabled' : 'status_disabled'))
                    },
                    {
                        xtype: 'button',
                        iconCls: 'pimcore_icon_delete',
                        cls: 'form_builder_cme_reset_button pimcore_button_transparent',
                        tooltip: t('form_builder.output_workflow.output_workflow_channel.email.mail_editor.reset'),
                        hidden: !hasMailLayoutData,
                        handler: function () {

                            Ext.Msg.confirm(t('delete'), t('form_builder.output_workflow.output_workflow_channel.email.mail_editor.really_delete'), function (btn) {

                                if (btn !== 'yes') {
                                    return;
                                }

                                if (this.mailTemplateData.hasOwnProperty(locale)) {
                                    delete this.mailTemplateData[locale];
                                }

                                this.changeMailEditorSignals(locale);
                            }.bind(this));
                        }.bind(this),
                        style: 'margin-left: 10px; filter:grayscale(100%);',
                    }
                ]
            }
        ]
    },

    changeMailEditorSignals: function (locale) {

        var hasData = this.mailTemplateData.hasOwnProperty(locale) && this.mailTemplateData[locale] !== '',
            statusButtons = this.panel.query('panel[cls~="form_builder_channel_mail_editor_panel_' + locale + '"] button[cls="form_builder_cme_status_button"]'),
            resetButtons = this.panel.query('panel[cls~="form_builder_channel_mail_editor_panel_' + locale + '"] button[cls~="form_builder_cme_reset_button"]');

        if (statusButtons.length > 0) {
            statusButtons[0].setStyle('background', hasData ? '#3e943e' : '#7f8a7f');
            statusButtons[0].setText(t('form_builder.output_workflow.output_workflow_channel.email.mail_editor.' + (hasData ? 'status_enabled' : 'status_disabled')));
        }

        if (resetButtons.length > 0) {
            resetButtons[0].setVisible(hasData);
        }
    },

    showMailEditor: function (locale) {

        var addParams = {channelId: this.channelId},
            callbacks = {
                loadData: function () {
                    var data = {};
                    if (this.mailTemplateData.hasOwnProperty(locale)) {
                        data[locale] = this.mailTemplateData[locale];
                        return data;
                    }

                    return '';

                }.bind(this),
                saveData: function (data) {

                    if (!data.hasOwnProperty(locale)) {
                        return;
                    }

                    this.mailTemplateData[locale] = data[locale];
                    this.changeMailEditorSignals(locale);

                }.bind(this)
            };

        new Formbuilder.extjs.extensions.formMailEditor(this.formId, 'output_workflow_channel', addParams, true, locale, callbacks);
    },

    isValid: function () {
        return this.panel.form.isValid();
    },

    getValues: function () {

        var formValues = this.panel.form.getValues();

        if (Ext.isObject(this.mailTemplateData)) {
            Ext.Object.each(this.mailTemplateData, function (locale, data) {
                if (data !== '') {
                    formValues[locale + '.mailLayoutData'] = data;
                }
            }.bind(this));
        }

        return formValues;
    }
});