pimcore.registerNS("Formbuilder.comp.type.image");
Formbuilder.comp.type.image = Class.create(Formbuilder.comp.type.base,{

    type: "image",

    getTypeName: function () {
        return t("image");
    },

    getIconClass: function () {
        return "Formbuilder_icon_image";
    },

    onAfterPopulate: function(){

        var label = Ext.getCmp("fieldlabel");
        var description = Ext.getCmp("fielddescription");
        var allowempty = Ext.getCmp("fieldallowempty");
        var required = Ext.getCmp("fieldrequired");
        var value = Ext.getCmp("fieldvalue");

        allowempty.hide();
        required.hide();
        value.hide();

    },

    getForm: function($super){
        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "image",
                fieldLabel: t("Image"),
                anchor: "100%"
            }

        ]
        });

        this.form.add(thisNode);

        return this.form;
    }

});