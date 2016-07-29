pimcore.registerNS("Formbuilder.comp.validator.identical");
Formbuilder.comp.validator.identical = Class.create(Formbuilder.comp.validator.base,{

    type: "identical",
    errors:["notSame","missingToken"],

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("identical");
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
                name: "token",
                fieldLabel: t("Token"),
                anchor: "100%",
                checked: this.datax.token
            }
        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});

