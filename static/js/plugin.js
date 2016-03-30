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

    pimcoreReady: function (params,broker){

        var user = pimcore.globalmanager.get("user");
        
        if(user.admin == true){

            var action = new Ext.Action({

                id:"Formbuilder_setting_button",
                text: t('formBuilder settings'),
                iconCls:"Formbuilder_icon_fbuilder",
                handler: function(){
                    var gestion = new Formbuilder.settings;
                }

            });

            layoutToolbar.extrasMenu.add(action);
        
        }
        this.getLanguages();
    },

    getLanguages: function(){

        Ext.Ajax.request({
            url: '/admin/settings/get-available-languages',
            scope:this,
            success: function (response) {
                var resp = Ext.util.JSON.decode(response.responseText);
                pimcore.globalmanager.add("Formbuilder.languages",resp);
            }
        });
    }

});

new pimcore.plugin.Formbuilder();
