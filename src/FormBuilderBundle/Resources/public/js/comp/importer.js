pimcore.registerNS('Formbuilder.comp.importer');

Formbuilder.comp.importer = Class.create({

    parentPanel: null,
    importId: null,
    uploadWindow: null,

    initialize: function (parentPanel) {
        this.parentPanel = parentPanel;
        this.importId = uniqid();
    },

    showPanel: function () {

        var url = '/admin/formbuilder/settings/import-form/' + this.importId,
            uploadForm;

        this.uploadWindow = new Ext.Window({
            autoHeight: true,
            title: t('upload'),
            closeAction: 'close',
            width: 400,
            modal: true
        });

        if (Ext.isFunction(pimcore.helpers.addCsrfTokenToUrl)) {
            url = pimcore.helpers.addCsrfTokenToUrl(url);
        }

        uploadForm = new Ext.form.FormPanel({
            bodyStyle: 'padding:10px',
            border: false,
            fileUpload: true,
            width: 400,
            items: [{
                xtype: 'fileuploadfield',
                emptyText: t('form_builder_upload_configuration_file'),
                fieldLabel: t('form_builder_file'),
                width: 300,
                name: 'formData',
                buttonText: '',
                buttonConfig: {
                    iconCls: 'pimcore_icon_upload'
                },
                listeners: {
                    change: function () {
                        uploadForm.getForm().submit({
                            url: url,
                            waitMsg: t('please_wait'),
                            success: this.getImportComplete.bind(this),
                            failure: function (el, data) {
                                this.uploadWindow.close();
                                Ext.Msg.alert(t('error'), data.response.responseText, 'error');
                            }.bind(this)
                        });
                    }.bind(this)
                }
            }]
        });

        this.uploadWindow.add(uploadForm);
        this.uploadWindow.show();
        this.uploadWindow.setWidth(400);
        this.uploadWindow.updateLayout();

    },

    /**
     * @param el
     * @param data
     */
    getImportComplete: function (el, data) {
        var response = Ext.decode(data.response.responseText);
        this.uploadWindow.close();
        if (response.success === true) {
            this.parentPanel.importForm(response.data);
        } else {
            Ext.Msg.alert(t('error'), response.message, 'error');
        }
    }

});