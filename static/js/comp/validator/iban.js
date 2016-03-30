pimcore.registerNS("Formbuilder.comp.validator.iban");
Formbuilder.comp.validator.iban = Class.create(Formbuilder.comp.validator.base,{

    type: "iban",
    errors:["ibanNotSupported","ibanFalseFormat","ibanCheckFailed"],

    initialize: function (treeNode, initData, parent) {



        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("iban");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();
        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "locale",
                fieldLabel: t("Locale"),
                anchor: "100%",
                allowBlank:false
            }


        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});
