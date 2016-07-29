pimcore.registerNS("Formbuilder.comp.filter.pregReplace");
Formbuilder.comp.filter.pregReplace = Class.create(Formbuilder.comp.filter.base,{

    type: "pregReplace",

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("pregReplace");
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
                xtype: "textfield",
                name: "matchPattern",
                fieldLabel: t("match Pattern"),
                anchor: "100%",
                value: this.datax.matchPattern
            },
            {
                xtype: "textfield",
                name: "replacement",
                fieldLabel: t("Replacement"),
                anchor: "100%",
                value: this.datax.replacement
            }
        ]
        });

        this.form.add(thisNode);

        return this.form;
    }
});