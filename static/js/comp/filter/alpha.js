pimcore.registerNS("Formbuilder.comp.filter.alpha");
Formbuilder.comp.filter.alpha = Class.create(Formbuilder.comp.filter.base,{

    type: "alpha",

    initialize: function (treeNode, initData, parent) {



        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("alpha");
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