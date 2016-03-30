pimcore.registerNS("Formbuilder.comp.type.button");
Formbuilder.comp.type.button = Class.create(Formbuilder.comp.type.base,{

    type: "button",

    getTypeName: function () {
        return t("button");
    },

    getIconClass: function () {
        return "Formbuilder_icon_button";
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
        return this.form;
    }

});