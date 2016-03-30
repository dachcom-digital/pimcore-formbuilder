pimcore.registerNS("Formbuilder.comp.filter.baseName");
Formbuilder.comp.filter.baseName = Class.create(Formbuilder.comp.filter.base,{

    type: "baseName",

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("baseName");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){
        $super();
        
        return this.form;
    }

});