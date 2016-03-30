pimcore.registerNS("Formbuilder.comp.filter.htmlEntities");
Formbuilder.comp.filter.htmlEntities = Class.create(Formbuilder.comp.filter.base,{

    type: "htmlEntities",

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("htmlEntities");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){
        $super();

        var entStore = new Ext.data.ArrayStore({
            fields: ["value","label"],
            data : [["0","NO_QUOTES"],["3","QUOTES"],["2","COMPAT"]]
        });


        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
                {
                xtype: "combo",
                name: "quoteStyle",
                fieldLabel: t("Quote style"),
                queryDelay: 0,
                displayField:"label",
                valueField: "value",
                mode: 'local',
                store: entStore,
                editable: false,
                triggerAction: 'all',
                anchor:"100%",
                value:"2"
            }

        ]
        });
        this.form.add(thisNode);
        return this.form;
    }

});