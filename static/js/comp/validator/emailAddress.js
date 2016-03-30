pimcore.registerNS("Formbuilder.comp.validator.emailAddress");
Formbuilder.comp.validator.emailAddress = Class.create(Formbuilder.comp.validator.base,{

    type: "emailAddress",
    errors:["emailAddressInvalid","emailAddressInvalidFormat","emailAddressInvalidHostname","emailAddressInvalidMxRecord","emailAddressInvalidSegment","emailAddressDotAtom","emailAddressQuotedString","emailAddressInvalidLocalPart","emailAddressLengthExceeded"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("emailAddress");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();

        return this.form;
    }
});