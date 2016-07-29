pimcore.registerNS("Formbuilder.comp.validator.ip");
Formbuilder.comp.validator.ip = Class.create(Formbuilder.comp.validator.base,{

    type: "ip",
    errors:["ipInvalid","notIpAddress"],

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("ip");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();

        return this.form;
    }

});