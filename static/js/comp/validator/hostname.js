pimcore.registerNS("Formbuilder.comp.validator.hostname");
Formbuilder.comp.validator.hostname = Class.create(Formbuilder.comp.validator.base,{

    type: "hostname",
    errors:["hostnameCannotDecodePunycode","hostnameInvalid","hostnameDashCharacter","hostnameInvalidHostname","hostnameInvalidHostnameSchema","hostnameInvalidLocalName","hostnameInvalidUri","hostnameIpAddressNotAllowed","hostnameLocalNameNotAllowed","hostnameUndecipherableTld","hostnameUnknownTld"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("hostname");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();

        return this.form;
    }

});