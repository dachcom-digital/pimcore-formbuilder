pimcore.registerNS("Formbuilder.comp.validator.graterThan");
Formbuilder.comp.validator.graterThan = Class.create(Formbuilder.comp.validator.base,{

    type: "graterThan",
    errors:["notGreaterThan"],

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("graterThan");
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
                xtype: "numberfield",
                name: "min",
                fieldLabel: t("Min value"),
                allowDecimals : true,
                anchor: "100%",
                value: this.datax.min
            }
        ]
        });

        this.form.add(thisNode);

        return this.form;
    }

});