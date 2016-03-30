pimcore.registerNS("Formbuilder.comp.filter.alnum");
Formbuilder.comp.filter.alnum = Class.create(Formbuilder.comp.filter.base,{

    type: "alnum",

    initialize: function (treeNode, initData, parent) {



        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("alnum");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_filter";
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