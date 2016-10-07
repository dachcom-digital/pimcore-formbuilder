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

    pimcoreReady: function (params,broker)
    {
        var user = pimcore.globalmanager.get("user");

        if(user.isAllowed('formbuilder_permission_settings')) {
            var formBuilderMenu = new Ext.Action({
                id:"Formbuilder_setting_button",
                text: t('formBuilder settings'),
                iconCls:"Formbuilder_icon_fbuilder",
                handler: this.openSettings
            });

            layoutToolbar.settingsMenu.add(formBuilderMenu);
        }
    },

    openSettings : function()
    {
        try {
            pimcore.globalmanager.get('Formbuilder_settings').activate();
        }
        catch (e) {
            pimcore.globalmanager.add('Formbuilder_settings', new Formbuilder.settings());
        }
    }

});

new pimcore.plugin.Formbuilder();
