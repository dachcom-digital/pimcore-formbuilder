pimcore.registerNS("pimcore.plugin.Formbuilder");
pimcore.plugin.Formbuilder = Class.create(pimcore.plugin.admin, {

    getClassName: function () {
        return "pimcore.plugin.Formbuilder";
    },

    initialize: function() {

        pimcore.plugin.broker.registerPlugin(this);

    },

    uninstall: function() {

    },

    pimcoreReady: function (params,broker) {

        var user = pimcore.globalmanager.get("user");

        if(user.admin == true){

            var formBuilderMenu = new Ext.Action({

                id:"Formbuilder_setting_button",
                text: t('formBuilder settings'),
                iconCls:"Formbuilder_icon_fbuilder",
                handler: this.openSettings

            });

            layoutToolbar.settingsMenu.add(formBuilderMenu);

        }

        this.getLanguages();
    },

    getLanguages: function() {

        Ext.Ajax.request({
            url: '/admin/settings/get-available-languages',
            scope:this,
            success: function (response) {
                var resp = Ext.util.JSON.decode(response.responseText);
                pimcore.globalmanager.add("Formbuilder.languages",resp);
            }
        });
    },

    openSettings : function() {

        try {
            pimcore.globalmanager.get('Formbuilder_settings').activate();
        }
        catch (e) {
            pimcore.globalmanager.add('Formbuilder_settings', new Formbuilder.settings());
        }

    }

});

new pimcore.plugin.Formbuilder();
