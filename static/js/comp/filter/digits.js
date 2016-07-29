pimcore.registerNS("Formbuilder.comp.filter.digits");
Formbuilder.comp.filter.digits = Class.create(Formbuilder.comp.filter.base,{

    type: "digits",

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("digits");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){

        $super();
        
        return this.form;
    }
});