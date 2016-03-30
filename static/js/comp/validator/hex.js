pimcore.registerNS("Formbuilder.comp.validator.hex");
Formbuilder.comp.validator.hex = Class.create(Formbuilder.comp.validator.base,{

    type: "hex",
    errors:["hexInvalid","notHex"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("hex");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();

        return this.form;
    }



});