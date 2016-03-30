pimcore.registerNS("Formbuilder.comp.type.hash");
Formbuilder.comp.type.hash = Class.create(Formbuilder.comp.type.base,{

    type: "hash",

    getTypeName: function () {
        return t("hash");
    },

    getIconClass: function () {
        return "Formbuilder_icon_hash";
    },

    getForm: function($super){
        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "salt",
                fieldLabel: t("Salt"),
                anchor: "100%"
            },
            {
                xtype: "numberfield",
                name: "timeout",
                fieldLabel: t("Timeout"),
                allowDecimals:false,
                anchor: "100%"
            }

        ]
        });

        this.form.add(thisNode);

        return this.form;
    }

});