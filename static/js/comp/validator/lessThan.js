pimcore.registerNS("Formbuilder.comp.validator.lessThan");
Formbuilder.comp.validator.lessThan = Class.create(Formbuilder.comp.validator.base,{

    type: "lessThan",
    errors:["notLessThan"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("lessThan");
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
                name: "max",
                fieldLabel: t("Max Value"),
                allowDecimals : true,
                anchor: "100%"
            }


        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});