pimcore.registerNS("Formbuilder.comp.type.password");
Formbuilder.comp.type.password = Class.create(Formbuilder.comp.type.base,{

    type: "password",

    getTypeName: function () {
        return t("password");
    },

    getIconClass: function () {
        return "Formbuilder_icon_password";
    },

    getForm: function($super){
        $super();

        return this.form;
    }

});