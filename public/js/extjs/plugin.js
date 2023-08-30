document.addEventListener(pimcore.events.pimcoreReady, (e) => {

    var user = pimcore.globalmanager.get('user'),
        openSettings = function (config) {
            try {
                pimcore.globalmanager.get('form_builder_settings').activate();
            } catch (e) {
                pimcore.globalmanager.add('form_builder_settings', new Formbuilder.settings(config));
            }
        };

    if (!user.isAllowed('formbuilder_permission_settings')) {
        return false;
    }

    Ext.Ajax.request({
        url: '/admin/formbuilder/settings/get-settings',
        success: function (response) {

            var config = Ext.decode(response.responseText),
                formBuilderMenu = new Ext.Action({
                    id: 'form_builder_setting_button',
                    text: t('form_builder_settings'),
                    iconCls: 'form_builder_icon_fbuilder',
                    handler: openSettings.bind(this, config)
                });

            if (layoutToolbar.settingsMenu) {
                layoutToolbar.settingsMenu.add(formBuilderMenu);
            }

        }.bind(this)
    });

});