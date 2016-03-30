pimcore.registerNS("Formbuilder.comp.type.submit");
Formbuilder.comp.type.submit = Class.create(Formbuilder.comp.type.base,{

    type: "submit",

    getTypeName: function () {
        return t("submit");
    },

    getIconClass: function () {
        return "Formbuilder_icon_submit";
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