pimcore.registerNS("Formbuilder.comp.type.checkbox");
Formbuilder.comp.type.checkbox = Class.create(Formbuilder.comp.type.base,{

    type: "checkbox",

    getTypeName: function () {
        return t("checkbox");
    },

    getIconClass: function () {
        return "Formbuilder_icon_checkbox";
    },

    getForm: function($super){
        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "checkedValue",
                fieldLabel: t("Checked value"),
                anchor: "100%"
            },
            {
                xtype: "textfield",
                name: "uncheckedValue",
                fieldLabel: t("Unchecked value"),
                anchor: "100%"
            },
            {
                xtype: "checkbox",
                name: "checked",
                fieldLabel: t("Checked"),
                checked:false
            }

        ]
        });



        this.form.add(thisNode);

        return this.form;
    }

});