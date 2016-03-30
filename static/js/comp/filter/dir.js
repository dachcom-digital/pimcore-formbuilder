pimcore.registerNS("Formbuilder.comp.filter.dir");
Formbuilder.comp.filter.dir = Class.create(Formbuilder.comp.filter.base,{

    type: "dir",

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("dir");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){
        $super();

        return this.form;
    }

});