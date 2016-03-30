Ext.Loader.setPath('Ext.ux', '/pimcore/static6/js/lib/ext/ux');

Ext.require([
    'Ext.ux.IFrame'
]);

pimcore.registerNS("Formbuilder.apiwindow");
Formbuilder.apiwindow = Class.create({
    
    initialize: function(path){
        this.src = path;
    },
    
    showWindow: function(){

        this.window = new Ext.Window({
            title: t('Api window'),
            layout:'fit',
            width:800,
            height:600,
            closeAction:'close',
            plain: true,
            modal: true,
            items : [

                new Ext.ux.IFrame({
                    src : this.src
                })

            ]

        });

        this.window.show();

    }

});