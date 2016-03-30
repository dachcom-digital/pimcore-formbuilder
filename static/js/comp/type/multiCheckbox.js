pimcore.registerNS("Formbuilder.comp.type.multiCheckbox");
Formbuilder.comp.type.multiCheckbox = Class.create(Formbuilder.comp.type.base,{

    type: "multiCheckbox",

    getTypeName: function () {
        return t("multiCheckbox");
    },

    getIconClass: function () {
        return "Formbuilder_icon_multiCheckbox";
    },

    getForm: function($super){
        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                    xtype: "textfield",
                    name: "separator",
                    fieldLabel: t("separator"),
                    anchor: "100%"
                },
                {
                xtype: "checkbox",
                name: "registerInArrayValidator",
                fieldLabel: t("registerInArrayValidator"),
                checked:false
            },
            new Ext.ux.form.SuperField({
                allowEdit: true,
                name: "multiOptions",
                stripeRows:false,
                values:this.datax.multiOptions,
                items: [
                {
                    xtype: "textfield",
                    name: "key",
                    fieldLabel: t("Option"),
                    anchor: "100%",
                    summaryDisplay:true,
                    allowBlank:false
                },
                {
                    xtype: "textfield",
                    name: "value",
                    fieldLabel: t("Value"),
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
    },
    
    getTranslatForm:function($super){
        $super();
        if(this.datax.multiOptions){
            var values = new Array();
        
            for (var i=0;i<this.datax.multiOptions.length;i++){
                values.push([this.datax.multiOptions[i]["key"],this.datax.multiOptions[i]["value"]]);
            };
        
            var storeMulti = new Ext.data.ArrayStore({
                fields: ["key","label"],
                data : values
            });
        }
        
        var trans = new Ext.form.FieldSet({
            title: t("multiOptions translation"),
            collapsible: true,
            defaultType: 'textfield',
            items:[new Ext.ux.form.SuperField({
                allowEdit: true,
                name: "multiOptions",
                stripeRows:false,
                values:this.datax.translate.multiOptions,
                items: [
                {
                    xtype: "combo",
                    name: "locale",
                    fieldLabel: t("Locale"),
                    queryDelay: 0,
                    displayField:"label",
                    valueField: "key",
                    mode: 'local',
                    store: this.localeStore,
                    editable: true,
                    triggerAction: 'all',
                    anchor:"100%",
                    summaryDisplay:true,
                    allowBlank:false
                },{
                    xtype: "combo",
                    name: "multiOptions",
                    fieldLabel: t("multiOptions"),
                    queryDelay: 0,
                    displayField:"label",
                    valueField: "label",
                    mode: 'local',
                    store: storeMulti,
                    editable: true,
                    triggerAction: 'all',
                    anchor:"100%",
                    summaryDisplay:true,
                    allowBlank:false
                },

                {
                    xtype: "textfield",
                    name: "value",
                    fieldLabel: t("value"),
                    anchor: "100%",
                    summaryDisplay:true
                }
                ]
            })

            ]
        });

        this.transForm.add(trans);
        
        return this.transForm;
        
    }

});