pimcore.registerNS("Formbuilder.comp.type.country");
Formbuilder.comp.type.country = Class.create(Formbuilder.comp.type.base,{

    type: "country",

    multiOptionStore : null,

    getTypeName: function () {
        return t("country");
    },

    getIconClass: function () {
        return "Formbuilder_icon_country";
    }

});