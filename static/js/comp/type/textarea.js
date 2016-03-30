pimcore.registerNS("Formbuilder.comp.type.textarea");
Formbuilder.comp.type.textarea = Class.create(Formbuilder.comp.type.base,{

    type: "textarea",

    getTypeName: function () {
        return t("textarea");
    },

    getIconClass: function () {
        return "Formbuilder_icon_textarea";
    },

    getForm: function($super){
        $super();

        return this.form;
    }

});