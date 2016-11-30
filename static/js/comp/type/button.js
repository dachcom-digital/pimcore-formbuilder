pimcore.registerNS("Formbuilder.comp.type.button");
Formbuilder.comp.type.button = Class.create(Formbuilder.comp.type.base,{

    type: "button",

    getTypeName: function () {
        return t("button");
    },

    getIconClass: function () {
        return "Formbuilder_icon_button";
    },

    onAfterPopulate: function(){

        var allowEmpty = this.form.getForm().findField("allowEmpty"),
            required = this.form.getForm().findField("required"),
            value = this.form.getForm().findField("value");

        allowEmpty.hide();
        required.hide();
        value.hide();

    },

    getForm: function($super){
        $super();
        return this.form;
    }

});