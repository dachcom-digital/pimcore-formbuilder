pimcore.registerNS('Formbuilder.comp.importer');

Formbuilder.comp.importer = Class.create({

    initialize: function (parentPanel) {
        this.parentPanel = parentPanel;
        this.importId = uniqid();
    },

    showPanel: function () {

        var _ = this,
            url = '/admin/formbuilder/settings/import-form/' + this.importId,
            uploadWindowCompatible = new Ext.Window({
            autoHeight: true,
            title: t('upload'),
            closeAction: 'close',
            width: 400,
            modal: true
        }), uploadForm;

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
                            success: function (el, res) {
                                _.getImport();
                                uploadWindowCompatible.close();
                            },
                            failure: function (el, res) {
                                uploadWindowCompatible.close();
                            }
                        });
                    }
                }
            }]
        });

        uploadWindowCompatible.add(uploadForm);
        uploadWindowCompatible.show();
        uploadWindowCompatible.setWidth(401);
        uploadWindowCompatible.updateLayout();

    },

    getImport: function () {
        Ext.Ajax.request({
            url: '/admin/formbuilder/settings/get-import',
            params: {
                id: this.importId,
                method: 'post'
            },
            success: this.getImportComplete.bind(this)
        });
    },

    getImportComplete: function (response) {
        var data = Ext.decode(response.responseText);
        this.parentPanel.importForm(data);
        pimcore.layout.refresh();
    }

});