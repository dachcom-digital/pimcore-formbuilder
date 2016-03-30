pimcore.registerNS("Formbuilder.comp.validator.postCode");
Formbuilder.comp.validator.postCode = Class.create(Formbuilder.comp.validator.base,{

    type: "postCode",
    errors:["postcodeInvalid","postcodeNoMatch"],

    initialize: function (treeNode, initData, parent) {



        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("postCode");
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
            },
            {
                xtype: "textfield",
                name: "format",
                fieldLabel: t("code post format"),
                anchor: "100%"
            }



        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});