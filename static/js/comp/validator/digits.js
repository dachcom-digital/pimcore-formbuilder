pimcore.registerNS("Formbuilder.comp.validator.digits");
Formbuilder.comp.validator.digits = Class.create(Formbuilder.comp.validator.base,{

    type: "digits",
    errors:["notDigits","digitsStringEmpty","digitsInvalid"],

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function() {
        return t("digits");
    },   
    
    getIconClass: function() {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super) {

        $super();

        return this.form;
    }

});