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

        var field = this.form.getForm().findField("destination");

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
                    name: "multiFile",
                    fieldLabel: t("multiFile"),
                    checked: false,
                    value: this.datax.multiFile,
                    listeners:{
                        change: function(checkbox, checked) {

                            var fbAllowedExtensions = this.form.getForm().findField("allowedExtensions"),
                                fbAllowedExtensionsLabel = fbAllowedExtensions.nextSibling(),
                                fbDestination = this.form.getForm().findField("destination");

                            if (checked) {

                                fbAllowedExtensions.show();
                                fbAllowedExtensionsLabel.show();
                                fbDestination.hide();

                            } else {

                                fbAllowedExtensions.hide();
                                fbAllowedExtensionsLabel.hide();
                                fbDestination.show();

                            }

                        }.bind(this)
                    }
                },
                {
                    xtype: "label",
                    style:'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
                    text: t("If your form is in ajax mode, we highly recommend to activate the multi file mode. It is safer, nicer and simple cool!")
                },
                {
                    xtype: "numberfield",
                    name: "maxFileSize",
                    fieldLabel: t("maxFileSize"),
                    labelWidth: 150,
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
                    labelWidth: 150,
                    queryMode: "allowedExtensions",
                    displayField: "allowedExtensions",
                    valueField: "allowedExtensions",
                    hideTrigger: true
                },
                {
                    xtype: "label",
                    name: "fbAllowedExtensionsLabel",
                    style:'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
                    text: t("Add some extensions and confirm with enter.")
                },
                {
                    xtype: "textfield",
                    name: "destination",
                    fieldLabel: t("destination"),
                    labelWidth: 150,
                    anchor: "100%",
                    value: this.datax.destination,
                    hidden: this.datax.multiFile,
                    listeners: {
                        scope: this,
                        change: function(field,newValue){
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