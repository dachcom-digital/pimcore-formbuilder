pimcore.registerNS("Formbuilder.comp.type.text");
Formbuilder.comp.type.text = Class.create(Formbuilder.comp.type.base,{

    type: "text",

    getTypeName: function () {
        return t("text");
    },

    getIconClass: function () {
        return "Formbuilder_icon_text";
    },

    getForm: function($super){
        $super();

        return this.form;
    }

});