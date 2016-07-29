pimcore.registerNS("Formbuilder.comp.filter.stringToUpper");
Formbuilder.comp.filter.stringToUpper = Class.create(Formbuilder.comp.filter.base,{

    type: "stringToUpper",

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("stringToUpper");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){

        $super();

        return this.form;
    }
});