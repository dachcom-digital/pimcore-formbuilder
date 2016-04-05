pimcore.registerNS("Formbuilder.comp.type.multiselect");
Formbuilder.comp.type.multiselect = Class.create(Formbuilder.comp.type.base,{

    type: "multiselect",

    multiOptionStore : null,

    getTypeName: function () {
        return t("multiselect");
    },

    getIconClass: function () {
        return "Formbuilder_icon_multiselect";
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
            };

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