pimcore.registerNS("Formbuilder.comp.validator.isbn");
Formbuilder.comp.validator.isbn = Class.create(Formbuilder.comp.validator.base,{

    type: "isbn",
    errors:["isbnInvalid","isbnNoIsbn"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("isbn");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();
        
        var isbnStore = new Ext.data.ArrayStore({
            fields: ["value","label"],
            data : [["auto","Auto"],["10","ISBN10"],["13","ISBN13"]]
        });

        
        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "separator",
                fieldLabel: t("Separator"),
                anchor: "100%"
            },
            {
                xtype: "combo",
                name: "type",
                fieldLabel: t("ISBN type"),
                queryDelay: 0,
                displayField:"label",
                valueField: "value",
                mode: 'local',
                store: isbnStore,
                editable: false,
                triggerAction: 'all',
                anchor:"100%",
                value:"auto"
            }

        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});