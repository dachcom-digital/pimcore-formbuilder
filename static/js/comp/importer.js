pimcore.registerNS("Formbuilder.comp.importer");

Formbuilder.comp.importer = Class.create({

    initialize: function (parentPanel) {
       this.parentPanel = parentPanel;
       this.importId = uniqid();

    },

    showPanel: function(){
        if(typeof success != "function") {
            var success = function () {  };
        }

        if(typeof failure != "function") {
            var failure = function () {};
        }

        var url =   '/plugin/Formbuilder/Settings/import?id=' + this.importId + '&pimcore_admin_sid=' + pimcore.settings.sessionId;

        var uploadWindowCompatible = new Ext.Window({
            autoHeight: true,
            title: t('Select Import'),
            closeAction: 'close',
            width:400,
            modal: true
        });
        var fbClass = this;
        var uploadForm = new Ext.form.FormPanel({
            layout: "pimcoreform",
            fileUpload: true,
            width: 400,
            bodyStyle: 'padding: 10px;',
            items: [{
                xtype: 'fileuploadfield',
                emptyText: t("select_a_file"),
                fieldLabel: t("Import File"),
                width: 230,
                name: 'Filedata',
                buttonText: "",
                buttonCfg: {
                    iconCls: 'pimcore_icon_upload_single'

                },
                listeners: {
                    fileselected: function () {
                        uploadForm.getForm().submit({
                            url: url,
                            waitMsg: t("please_wait"),
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
        uploadWindowCompatible.doLayout();

    },
    getImport: function () {

        Ext.Ajax.request({
            url: "/plugin/Formbuilder/Settings/getimport",
            params: {
                id: this.importId,
                method: "post"
            },
            success: this.getImportComplete.bind(this)
        });
    },

    getImportComplete: function (response) {

         var data = Ext.decode(response.responseText);

        this.parentPanel.importation(data);
        pimcore.layout.refresh();


    }

});