pimcore.registerNS("Formbuilder.comp.filter.boolean");
Formbuilder.comp.filter.boolean = Class.create(Formbuilder.comp.filter.base,{

    type: "boolean",

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("boolean");
    },

    getIconClass: function () {
        return "Formbuilder_icon_filter";
    },

    getForm: function($super){
        $super();

        var typeStore = new Ext.data.ArrayStore({
            fields: ["value","label"],
            data : [["1","boolean"],["2","integer"],["4","float"],["8","string"],["16","zero"],["32","empty array"],["64","null"],["127","php"],["128","false string"],["256","yes"],["511","all"]]
        });


        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
            {
                xtype: 'tagfield',
                name: "type",
                allowBlank:false,
                queryDelay: 0,
                triggerAction: 'all',
                resizable: true,
                mode: 'local',
                anchor:'100%',
                minChars: 1,
                removeValuesFromStore:false,
                fieldLabel: t("boolean type"),
                emptyText: t("Choose the boolean types"),
                store: typeStore,
                displayField: "label",
                valueField: "value",
                value: this.datax.type
            }


        ]
        });
        this.form.add(thisNode);
        return this.form;
    }

});