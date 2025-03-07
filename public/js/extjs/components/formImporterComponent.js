pimcore.registerNS('Formbuilder.extjs.components.formImporter');

Formbuilder.extjs.components.formImporter = Class.create({

    parentPanel: null,
    importId: null,
    uploadWindow: null,

    initialize: function (parentPanel) {
        this.parentPanel = parentPanel;
        this.importId = uniqid();
    },

    showPanel: function () {

        var uploadForm,
            requestParams = {};

        this.uploadWindow = new Ext.Window({
            autoHeight: true,
            title: t('upload'),
            closeAction: 'close',
            width: 600,
            modal: true
        });

        if (pimcore.hasOwnProperty('settings') && pimcore.settings.hasOwnProperty('csrfToken')) {
            requestParams = {
                csrfToken: pimcore.settings['csrfToken'],
                formId: this.parentPanel.formId
            };
        }

        uploadForm = new Ext.form.FormPanel({
            bodyStyle: 'padding:10px',
            border: false,
            fileUpload: true,
            autoHeight: true,
            items: [
                {
                    xtype: 'fieldset',
                    title: t('form_builder_upload_configuration.section'),
                    collapsible: false,
                    collapsed: false,
                    autoHeight: true,
                    defaults: {
                        labelWidth: 170
                    },
                    name: 'options',
                    items: [
                        {
                            xtype: 'label',
                            style: 'display:block; padding:5px; background:#fff; border:1px solid #eee; font-weight: 300;',
                            text: t('form_builder.import_note')
                        },
                        {
                            xtype: 'checkbox',
                            value: true,
                            inputValue: true,
                            uncheckedValue: false,
                            fieldLabel: t('form_builder_upload_configuration.section.output_workflows'),
                            name: 'outputWorkflows',
                        },
                        {
                            xtype: 'checkbox',
                            value: true,
                            inputValue: true,
                            uncheckedValue: false,
                            fieldLabel: t('form_builder_upload_configuration.section.conditional_logic'),
                            name: 'conditionalLogic',
                        }
                    ]
                },
                {
                    xtype: 'fileuploadfield',
                    emptyText: t('form_builder_upload_configuration.file'),
                    fieldLabel: t('form_builder_file'),
                    name: 'formData',
                    allowBlank: false,
                    width: '100%',
                    buttonConfig: {
                        iconCls: 'pimcore_icon_upload'
                    }
                },
                {
                    xtype: 'button',
                    text: t('import'),
                    iconCls: 'pimcore_icon_import',
                    handler: function (b) {

                        if (!uploadForm.isValid()) {
                            return;
                        }

                        b.setDisabled(true);

                        uploadForm.getForm().submit({
                            url: Routing.generate('form_builder.controller.admin.import_form', {id: this.importId}),
                            params: Ext.Object.merge(requestParams, uploadForm.getValues()),
                            waitMsg: t('please_wait'),
                            success: this.getImportComplete.bind(this),
                            failure: function (el, data) {

                                var response;

                                this.uploadWindow.close();
                                response = Ext.decode(data.response.responseText);

                                Ext.Msg.alert(
                                    t('error'),
                                    response && response.hasOwnProperty('message')
                                        ? response.message
                                        : data.response.responseText
                                );
                            }.bind(this)
                        });
                    }.bind(this)
                }
            ]
        });

        this.uploadWindow.add(uploadForm);
        this.uploadWindow.show();
        this.uploadWindow.setWidth(400);
        this.uploadWindow.updateLayout();
    },

    getImportComplete: function (el, data) {

        var response = Ext.decode(data.response.responseText);
        this.uploadWindow.close();

        if (response.success === true) {
            this.parentPanel.importForm(response.formId);
        } else {
            Ext.Msg.alert(t('error'), response.message);
        }
    }
});