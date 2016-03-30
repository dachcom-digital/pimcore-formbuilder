pimcore.registerNS("Formbuilder.comp.validator.alnum");
Formbuilder.comp.validator.alnum = Class.create(Formbuilder.comp.validator.base,{

    type: "alnum",
    errors:["alnumInvalid","notAlnum","alnumStringEmpty"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("alnum");
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
                name: "allowWhiteSpace",
                fieldLabel: t("AllowWhiteSpace"),
                checked:false
            }

        ]
        });
        this.form.add(thisNode);
        return this.form;
    }

});