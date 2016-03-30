pimcore.registerNS("Formbuilder.comp.type.reset");
Formbuilder.comp.type.reset = Class.create(Formbuilder.comp.type.base,{

    type: "reset",

    getTypeName: function () {
        return t("reset");
    },

    getIconClass: function () {
        return "Formbuilder_icon_reset";
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