pimcore.registerNS("Formbuilder.comp.filter.stringTrim");
Formbuilder.comp.filter.stringTrim = Class.create(Formbuilder.comp.filter.base,{

    type: "stringTrim",

    initialize: function(treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function() {
        return t("stringTrim");
    },

    getIconClass: function() {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super) {

        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "charList",
                fieldLabel: t("Char list"),
                anchor: "100%",
                value: this.datax.charList
            }
        ]
        });

        this.form.add(thisNode);

        return this.form;
    }
});