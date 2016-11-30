pimcore.registerNS("Formbuilder.comp.type.reset");
Formbuilder.comp.type.reset = Class.create(Formbuilder.comp.type.base,{

    type: "reset",

    getTypeName: function () {
        return t("reset");
    },

    getIconClass: function () {
        return "Formbuilder_icon_reset";
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