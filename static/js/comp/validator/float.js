pimcore.registerNS("Formbuilder.comp.validator.float");
Formbuilder.comp.validator.float = Class.create(Formbuilder.comp.validator.base,{

    type: "float",
    errors:["floatInvalid","notFloat"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("float");
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
                anchor: "100%"
            }
        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});