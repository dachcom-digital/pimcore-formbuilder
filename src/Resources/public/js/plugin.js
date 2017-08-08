pimcore.registerNS('pimcore.plugin.Formbuilder');

pimcore.plugin.Formbuilder = Class.create(pimcore.plugin.admin, {

    config : {},

    getClassName: function () {
        return 'pimcore.plugin.Formbuilder';
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },

    uninstall: function() {

    },

    pimcoreReady: function (params,broker)
    {
        var _ = this,
            user = pimcore.globalmanager.get('user');

        if(!user.isAllowed('formbuilder_permission_settings')) {
            return false;
        }

        Ext.Ajax.request({
            url: '/admin/formbuilder/settings/get-settings',
            success: function (response)
            {
                _.config = Ext.decode(response.responseText);

                var formBuilderMenu = new Ext.Action({
                    id: 'form_builder_setting_button',
                    text: t('form_builder_settings'),
                    iconCls: 'form_builder_icon_fbuilder',
                    handler: this.openSettings.bind(this)
                });

                layoutToolbar.settingsMenu.add(formBuilderMenu);

            }.bind(this)
        });
    },

    openSettings : function()
    {
        var _ = this;

        try {
            pimcore.globalmanager.get('form_builder_settings').activate();
        }
        catch (e) {
            pimcore.globalmanager.add('form_builder_settings', new Formbuilder.settings(_.config));
        }
    }

});

new pimcore.plugin.Formbuilder();