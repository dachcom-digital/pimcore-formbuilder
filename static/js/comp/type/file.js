pimcore.registerNS("Formbuilder.comp.type.file");
Formbuilder.comp.type.file = Class.create(Formbuilder.comp.type.base,{

    type: "file",

    getTypeName: function () {
        return t("file");
    },

    getIconClass: function () {
        return "Formbuilder_icon_file";
    },

    onAfterPopulate: function(){

        var field = Ext.getCmp("destination");
        this.checkPath(field.getValue(),field);
    },

    getForm: function($super){
        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
                {
                    id:"destination",
                    xtype: "textfield",
                    name: "destination",
                    fieldLabel: t("destination"),
                    anchor: "100%",
                    value: this.datax.destination,
                    listeners: {
                        scope:this,
                        'change': function(field,newValue,oldValue,Object){
                            this.checkPath(newValue,field);
                        }
                    }
                },
                {
                    xtype: "numberfield",
                    name: "maxFileSize",
                    fieldLabel: t("maxFileSize"),
                    allowDecimals:false,
                    value: this.datax.maxFileSize
                },
                {
                    xtype: "checkbox",
                    name: "multiFile",
                    fieldLabel: t("multiFile"),
                    checked: false,
                    value: this.datax.multiFile
                }

            ]
        });

        this.form.add(thisNode);

        return this.form;
    }

});