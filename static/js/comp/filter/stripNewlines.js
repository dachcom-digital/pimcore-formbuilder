pimcore.registerNS("Formbuilder.comp.filter.stripNewlines");
Formbuilder.comp.filter.stripTags = Class.create(Formbuilder.comp.filter.base,{

    type: "stripNewlines",

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function() {
        return t("stripNewlines");
    },

    getIconClass: function() {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super) {

        $super();

        return this.form;
    }

});