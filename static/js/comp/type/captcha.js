pimcore.registerNS("Formbuilder.comp.type.captcha");
Formbuilder.comp.type.captcha = Class.create(Formbuilder.comp.type.base,{

    type: "captcha",

    getTypeName: function () {
        return t("captcha");
    },

    getIconClass: function () {
        return "Formbuilder_icon_captcha";
    },

    onAfterPopulate: function(){

        var wordFS = Ext.getCmp('wordFS');
        var imageFS = Ext.getCmp('imageFS');
        var reCaptchaFS = Ext.getCmp('reCaptchaFS');
        var combo = Ext.getCmp('captchaCombo');

        switch(combo.getValue()){
            case "dumb" :
                wordFS.show();
                imageFS.hide();
                reCaptchaFS.hide();
                break;
            case "figlet" :
                wordFS.show();
                imageFS.hide();
                reCaptchaFS.hide();
                break;
            case "image" :
                wordFS.show();
                imageFS.show();
                reCaptchaFS.hide();
                break;
            case "reCaptcha" :
                wordFS.hide();
                imageFS.hide();
                reCaptchaFS.show();
                break;
            default:
                wordFS.hide();
                imageFS.hide();
                reCaptchaFS.hide();
                break;
        }
        
        var imgDir = Ext.getCmp("imgDir");
        if(imageFS.hidden == false){
            this.checkPath(imgDir.getValue(),imgDir);
        }
        
    },

    addCaptchaFS: function(form){

        var word = new Ext.form.FieldSet({

            id:"wordFS",
            title: t("captcha word options"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
            {
                xtype: "numberfield",
                name: "captchaOptions.wordLen",
                fieldLabel: t("wordLen"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.wordLen']
            },
            {
                xtype: "numberfield",
                name: "captchaOptions.timeout",
                fieldLabel: t("timeout"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.timeout']
            },
            {
                xtype: "checkbox",
                name: "captchaOptions.useNumbers",
                fieldLabel: t("useNumbers"),
                checked:false,
                value:this.datax['captchaOptions.useNumbers']
            }
            ]
        });

        form.add(word);

        var image = new Ext.form.FieldSet({
            id:"imageFS",
            title: t("image options"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
            {
                xtype: "numberfield",    
                name: "captchaOptions.expiration",
                fieldLabel: t("expiration"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.expiration']
            },
            {
                xtype: "textfield",
                name: "captchaOptions.font",
                fieldLabel: t("font"),
                anchor: "100%",
                value:this.datax['captchaOptions.font']
            },
            {
                xtype: "numberfield",    
                name: "captchaOptions.fontSize",
                fieldLabel: t("font Size"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.fontSize']
            },
            {
                xtype: "numberfield",    
                name: "captchaOptions.height",
                fieldLabel: t("height"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.height']
            },
            {
                xtype: "numberfield",    
                name: "captchaOptions.width",
                fieldLabel: t("width"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.width']
            },
            {
                id:"imgDir",
                xtype: "textfield",
                name: "captchaOptions.imgDir",
                fieldLabel: t("image directory"),
                anchor: "100%",
                value:this.datax['captchaOptions.imgDir'],
                listeners:{
                    scope:this,
                    'change': function(field,newValue,oldValue,Object){
                        var ctr = Ext.getCmp("imageFS");
                        if(ctr.hidden == false){
                            this.checkPath(newValue,field);
                        }
                    }
                }
            },
            {
                xtype: "textfield",
                name: "captchaOptions.imgUrl",
                fieldLabel: t("Image Url"),
                anchor: "100%",
                value:this.datax['captchaOptions.imgUrl']
            },
            {
                xtype: "textfield",
                name: "captchaOptions.suffix",
                fieldLabel: t("image suffix"),
                anchor: "100%",
                value:this.datax['captchaOptions.suffix']
            },
            {
                xtype: "numberfield",    
                name: "captchaOptions.dotNoiseLevel",
                fieldLabel: t("dot noise level"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.dotNoiseLevel']
            },
            {
                xtype: "numberfield",    
                name: "captchaOptions.lineNoiseLevel",
                fieldLabel: t("Line noise level"),
                allowDecimals:false,
                anchor: "100%",
                value:this.datax['captchaOptions.lineNoiseLevel']
            }
            ]
        });
        form.add(image);

        var reCaptcha = new Ext.form.FieldSet({
            id:"reCaptchaFS",
            title: t("reCaptcha options"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "captchaOptions.secretKey",
                fieldLabel: t("Private key"),
                anchor: "100%",
                value:this.datax['captchaOptions.secretKey']
            },
            {
                xtype: "textfield",
                name: "captchaOptions.siteKey",
                fieldLabel: t("Public key"),
                anchor: "100%",
                value:this.datax['captchaOptions.siteKey']
            }
            ]
        });
        form.add(reCaptcha);

    },

    getForm: function($super){

        $super();

        var captchaStore = new Ext.data.ArrayStore({
            fields: ["value","label"],
            data : [["dumb","Dumb"],["figlet","Figlet"],["image","Image"],["reCaptcha","ReCaptcha"]]
        });

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                id:"captchaCombo",
                xtype: "combo",
                name: "captcha",
                fieldLabel: t("captcha type"),
                queryDelay: 0,
                displayField:"label",
                valueField: "value",
                mode: 'local',
                store: captchaStore,
                editable: false,
                triggerAction: 'all',
                anchor:"100%",
                value:this.datax.captcha,
                allowBlank:false,
                listeners:{
                    scope:this,
                    'select': function(combo,record,index){
                        var wordFS = Ext.getCmp('wordFS');
                        var imageFS = Ext.getCmp('imageFS');
                        var reCaptchaFS = Ext.getCmp('reCaptchaFS');

                        switch(record.data.value){
                            case "dumb" :
                                wordFS.show();
                                imageFS.hide();
                                reCaptchaFS.hide();
                                break;
                            case "figlet" :
                                wordFS.show();
                                imageFS.hide();
                                reCaptchaFS.hide();
                                break;
                            case "image" :
                                wordFS.show();
                                imageFS.show();
                                reCaptchaFS.hide();
                                break;
                            case "reCaptcha" :
                                wordFS.hide();
                                imageFS.hide();
                                reCaptchaFS.show();
                                break;
                            default:
                                wordFS.hide();
                                imageFS.hide();
                                reCaptchaFS.hide();
                                break;
                        }

                    }
                }
            }

            ]
        });

        this.form.add(thisNode);
        this.addCaptchaFS(this.form);
        
        return this.form;
    }

});