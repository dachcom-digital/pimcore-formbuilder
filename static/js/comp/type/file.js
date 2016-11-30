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
        if( field && !this.datax.multiFile ) {
            this.checkPath(field.getValue(),field);
        }
    },

    getForm: function($super){
        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
                {
                    xtype: "checkbox",
                    id: "fbMultfile",
                    name: "multiFile",
                    fieldLabel: t("multiFile"),
                    checked: false,
                    value: this.datax.multiFile,
                    listeners:{
                        change: function(checkbox, checked) {
                            if (checked) {
                                Ext.getCmp('fbAllowedExtensions').show();
                                Ext.getCmp('fbDestination').hide();
                            } else {
                                Ext.getCmp('fbAllowedExtensions').hide();
                                Ext.getCmp('fbDestination').show();

                            }
                        }
                    }
                },
                {
                    xtype: "label",
                    style:'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
                    text: t("If your form is in ajax mode, we highly recommend to activate the multi file mode. It is safer, nicer and simple cool!")
                },
                {
                    xtype: "numberfield",
                    id: "fbMaxFileSize",
                    name: "maxFileSize",
                    fieldLabel: t("maxFileSize"),
                    allowDecimals:false,
                    value: this.datax.maxFileSize
                },
                {
                    xtype: "label",
                    style:'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
                    text: t("Max file size will be calculated in MB. Empty or Zero means no Limit!")
                },
                {
                    xtype: "tagfield",
                    id: "fbAllowedExtensions",
                    name: "allowedExtensions",
                    fieldLabel: t("allowedExtensions"),
                    store: new Ext.data.ArrayStore({
                        fields: [
                            "allowedExtensions"
                        ],
                        data : [

                        ]
                    }),
                    value: this.datax.allowedExtensions,
                    createNewOnEnter: true,
                    createNewOnBlur: true,
                    queryMode: "allowedExtensions",
                    displayField: "allowedExtensions",
                    valueField: "allowedExtensions",
                    hideTrigger: true
                },
                {
                    xtype: "label",
                    style:'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
                    text: t("Add some extensions and confirm with enter.")
                },
                {
                    xtype: "textfield",
                    id:"fbDestination",
                    name: "destination",
                    fieldLabel: t("destination"),
                    anchor: "100%",
                    value: this.datax.destination,
                    hidden: this.datax.multiFile,
                    listeners: {
                        scope:this,
                        'change': function(field,newValue){
                            this.checkPath(newValue,field);
                        }
                    }
                }

            ]
        });

        this.form.add(thisNode);

        return this.form;
    }

});