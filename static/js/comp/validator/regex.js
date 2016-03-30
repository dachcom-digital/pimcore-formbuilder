pimcore.registerNS("Formbuilder.comp.validator.regex");
Formbuilder.comp.validator.regex = Class.create(Formbuilder.comp.validator.base,{

    type: "regex",
    errors:["regexInvalid","regexNotMatch","regexErrorous"],

    initialize: function (treeNode, initData, parent) {



        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("regex");
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
                name: "pattern",
                fieldLabel: t("Pattern"),
                anchor: "100%"
            }


        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});