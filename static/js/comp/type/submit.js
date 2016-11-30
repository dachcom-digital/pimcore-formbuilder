pimcore.registerNS("Formbuilder.comp.type.submit");
Formbuilder.comp.type.submit = Class.create(Formbuilder.comp.type.base,{

    type: "submit",

    getTypeName: function () {
        return t("submit");
    },

    getIconClass: function () {
        return "Formbuilder_icon_submit";
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