pimcore.registerNS("Formbuilder.comp.validator.creditCard");
Formbuilder.comp.validator.creditCard = Class.create(Formbuilder.comp.validator.base,{

    type: "creditCard",
    errors:["creditcardChecksum","creditcardContent","creditcardInvalid","creditcardLength","creditcardPrefix","creditcardService","creditcardServiceFailure"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("creditCard");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();
        
        var cbStore = new Ext.data.ArrayStore({
            fields: ["value","label"],
            data : [["All","All"],["American_Express","American Express"],["Unionpay","Unionpay"],["Diners_Club","Diners Club"],["Diners_Club_US","Diners Club US"],["Discover","Discover"],["JCB","JCB"],["Laser","Laser"],["Maestro","Maestro"],["Mastercard","Mastercard"],["Solo","Solo"],["Visa","Visa"]]
        });

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: 'superboxselectspe',
                name: "type",
                allowBlank:true,
                queryDelay: 0,
                triggerAction: 'all',
                resizable: true,
                mode: 'local',
                anchor:'100%',
                minChars: 1,
                removeValuesFromStore:false,
                fieldLabel: t("CB type"),
                emptyText: t("Choose credit cards"),
                store: cbStore,
                displayField: "label",
                valueField: "value"
            }

        ]
        });
        this.form.add(thisNode);
        return this.form;
    }

});