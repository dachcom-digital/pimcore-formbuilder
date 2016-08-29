pimcore.registerNS("Formbuilder.comp.type.multiCheckbox");
Formbuilder.comp.type.multiCheckbox = Class.create(Formbuilder.comp.type.base,{

    type: "multiCheckbox",

    multiOptionStore : null,

    getTypeName: function () {
        return t("multiCheckbox");
    },

    getIconClass: function () {
        return "Formbuilder_icon_multiCheckbox";
    },

    getForm: function($super){

        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "separator",
                fieldLabel: t("separator"),
                anchor: "100%"
            },
                {
                    xtype: "checkbox",
                    name: "registerInArrayValidator",
                    fieldLabel: t("registerInArrayValidator"),
                    checked:false
                },
                {
                    xtype: "checkbox",
                    name: "inline",
                    fieldLabel: t("show inline fields"),
                    checked:false,
                    value: this.datax.inline
                },

                this.generateMultiOptionsRepeaterField()

            ]
        });

        this.form.add(thisNode);

        return this.form;
    },

    getTranslateForm: function($super){

        $super();

        if(this.datax.multiOptions){

            var values = [];

            for (var i=0;i<this.datax.multiOptions.length;i++){
                values.push([this.datax.multiOptions[i]["value"],this.datax.multiOptions[i]["value"]]);
            }

            this.multiOptionStore = new Ext.data.ArrayStore({
                fields: ["key","label"],
                data : values
            });
        }

        var trans = new Ext.form.FieldSet({
            title: t("multiOptions translation"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
                this.generateLocaleRepeaterField('multiOptions')
            ]
        });

        this.transForm.add(trans);

        return this.transForm;

    }

});