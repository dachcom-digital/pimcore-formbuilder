pimcore.registerNS("Formbuilder.comp.type.hidden");
Formbuilder.comp.type.hidden = Class.create(Formbuilder.comp.type.base,{

    type: "hidden",

    getTypeName: function () {
        return t("hidden");
    },

    getIconClass: function () {
        return "Formbuilder_icon_hidden";
    },

    getForm: function($super){
        $super();


        return this.form;
    }

});