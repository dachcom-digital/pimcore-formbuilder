pimcore.registerNS("Formbuilder.comp.validator.int");
Formbuilder.comp.validator["int"] = Class.create(Formbuilder.comp.validator.base,{

    type: "int",
    errors:["intInvalid","notInt"],

    initialize: function (treeNode, initData, parent) {



        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("int");
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