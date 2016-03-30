pimcore.registerNS("Formbuilder.comp.filter.callback");
Formbuilder.comp.filter.callback = Class.create(Formbuilder.comp.filter.base,{

    type: "callback",

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("callback");
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
                name: "callback",
                fieldLabel: t("Callback"),
                anchor: "100%"
            }

        ]
        });
        this.form.add(thisNode);
        return this.form;
    }

});