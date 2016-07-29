pimcore.registerNS("Formbuilder.comp.filter.int");
Formbuilder.comp.filter["int"] = Class.create(Formbuilder.comp.filter.base,{

    type: "int",

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("int");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){

        $super();

        return this.form;
    }
});