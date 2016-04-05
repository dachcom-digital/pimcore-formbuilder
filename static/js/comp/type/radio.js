pimcore.registerNS("Formbuilder.comp.type.radio");
Formbuilder.comp.type.radio = Class.create(Formbuilder.comp.type.base,{

    type: "radio",

    multiOptionStore : null,

    getTypeName: function () {
        return t("radio");
    },

    getIconClass: function () {
        return "Formbuilder_icon_radio";
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

    getTranslatForm: function($super){

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