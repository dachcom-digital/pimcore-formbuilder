pimcore.registerNS('Formbuilder.comp.importer');

Formbuilder.comp.importer = Class.create({

    initialize: function (parentPanel) {
       this.parentPanel = parentPanel;
       this.importId = uniqid();

    },

    showPanel: function() {

        if(typeof success !== 'function') {
            var success = function () {  };
        }

        if(typeof failure !== 'function') {
            var failure = function () {};
        }

        var url = '/admin/formbuilder/settings/import-form/' + this.importId + '/' + pimcore.settings.sessionId;

        var uploadWindowCompatible = new Ext.Window({
            autoHeight: true,
            title: t('upload'),
            closeAction: 'close',
            width:400,
            modal: true
        });

        var fbClass = this;
        var uploadForm = new Ext.form.FormPanel({
            bodyStyle: 'padding:10px',
            border: false,
            fileUpload: true,
            width: 400,
            items: [{
                xtype: 'fileuploadfield',
                emptyText: t('select_a_file'),
                fieldLabel: t('file'),
                width: 230,
                name: 'Filedata',
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

                                fbClass.getImport();
                                uploadWindowCompatible.close();
                            },
                            failure: function (el, res) {

                                failure(res);
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