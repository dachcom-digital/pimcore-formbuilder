pimcore.registerNS("Formbuilder.comp.validator.inArray");
Formbuilder.comp.validator.inArray = Class.create(Formbuilder.comp.validator.base,{

    type: "inArray",
    errors:["notInArray"],

    initialize: function (treeNode, initData, parent) {



        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("inArray");
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
                name: "strict",
                fieldLabel: t("Strict"),
                checked:false
            },
                new Ext.ux.form.SuperField({
                allowEdit: true,
                name: "haystack",
                stripeRows:false,
                values:this.datax.haystack,
                items: [{
                    xtype: "textfield",
                    name: "key",
                    fieldLabel: t("Key"),
                    anchor: "100%",
                    summaryDisplay:true,
                    allowBlank:false
                },
                {
                    xtype: "textfield",
                    name: "value",
                    fieldLabel: t("value"),
                    anchor: "100%",
                    summaryDisplay:true,
                    allowBlank:false
                }


            ]
            })            
            



        ]
        });
        this.form.add(thisNode);
        return this.form;
    }



});