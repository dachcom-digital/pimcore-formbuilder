pimcore.registerNS("Formbuilder.comp.filter.stripTags");
Formbuilder.comp.filter.stripTags = Class.create(Formbuilder.comp.filter.base,{

    type: "stripTags",

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("stripTags");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){
        $super();
        
        return this.form;
    }

});