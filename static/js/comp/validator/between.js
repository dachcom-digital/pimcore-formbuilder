pimcore.registerNS("Formbuilder.comp.validator.between");
Formbuilder.comp.validator.between = Class.create(Formbuilder.comp.validator.base,{

    type: "between",
    errors:["notBetween","notBetweenStrict"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("between");
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
                xtype: "checkbox",
                name: "inclusive",
                fieldLabel: t("Inclusive"),
                checked:true
            },
            {
                xtype: "numberfield",
                name: "min",
                fieldLabel: t("min value"),
                allowDecimals : true,
                anchor: "100%"
            },
            {
                xtype: "numberfield",
                name: "max",
                fieldLabel: t("max value"),
                allowDecimals : true,
                anchor: "100%"
            }

        ]
        });
        this.form.add(thisNode);
        return this.form;
    }

});