pimcore.registerNS("Formbuilder.comp.filter.stringToLower");
Formbuilder.comp.filter.stringToLower = Class.create(Formbuilder.comp.filter.base,{

    type: "stringToLower",

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("stringToLower");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){

        $super();

        return this.form;

    }
});