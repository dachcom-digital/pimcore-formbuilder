pimcore.registerNS("Formbuilder.comp.type.captcha");
Formbuilder.comp.type.captcha = Class.create(Formbuilder.comp.type.base,{

    type: "captcha",

    showTranslationTab: false,

    getTypeName: function () {
        return t("captcha");
    },

    getIconClass: function () {
        return "Formbuilder_icon_captcha";
    },

    onAfterPopulate: function(){

        var wordFS = this.wordFS,
            imageFS = this.imageFS,
            reCaptchaFS = this.reCaptchaFS,
            combo = this.form.getForm().findField("captcha"),
            imgDir = this.form.getForm().findField("captchaOptions.imgDir");

        switch( combo.getValue() ) {

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

        if(imageFS.hidden === false) {
            this.checkPath(imgDir.getValue(), imgDir);
        }

    },

    addCaptchaFS: function() {

        var _me = this;

        this.wordFS = new Ext.form.FieldSet({

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

        this.form.add( this.wordFS );

        this.imageFS = new Ext.form.FieldSet({

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
                    xtype: "textfield",
                    name: "captchaOptions.imgDir",
                    fieldLabel: t("image directory"),
                    anchor: "100%",
                    value:this.datax['captchaOptions.imgDir'],
                    listeners:{
                        scope:this,
                        'change': function(field,newValue,oldValue,Object){
                            var ctr = _me.imageFS;
                            if(ctr.hidden === false){
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

        this.form.add( this.imageFS );

        this.reCaptchaFS = new Ext.form.FieldSet({

            name:"reCaptchaFS",
            title: t("reCaptcha options"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
                {
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

        this.form.add( this.reCaptchaFS );

    },

    getForm: function() {

        this.form = new Ext.form.FormPanel({
            bodyStyle: "padding: 10px;",
            labelWidth: 150,
            defaultType: 'textfield',
            items: [ this.getHookForm() ,{
                xtype:'fieldset',
                title: t('base settings'),
                collapsible: true,
                autoHeight:true,
                defaultType: 'textfield',
                items:[
                    {
                        xtype:"button",
                        text: t("View API"),
                        iconCls: "pimcore_icon_api",
                        handler: this.viewApi.bind(this),
                        style:{marginBottom : "5px"}
                    },
                    {
                        xtype: "textfield",
                        fieldLabel: t("name"),
                        name: "name",
                        value: this.datax.name,
                        allowBlank:false,
                        anchor: "100%",
                        enableKeyEvents: true
                    }

                ]

            }]

        });

        var _me = this,
            captchaStore = new Ext.data.ArrayStore(
                {
                    fields: ["value","label"],
                    data : [["dumb","Dumb"],["figlet","Figlet"],["image","Image"],["reCaptcha","ReCaptcha"]]
                }
            ),
            thisNode = new Ext.form.FieldSet({
                    title: t("This node"),
                    collapsible: true,
                    defaultType: 'textfield',
                    items:[
                        {
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
                            listeners: {
                                scope:this,
                                select: function(combo,record,index) {

                                    var wordFS = _me.wordFS,
                                        imageFS = _me.imageFS,
                                        reCaptchaFS = _me.reCaptchaFS;

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
                }
            );

        this.form.add(thisNode);
        this.addCaptchaFS();

        return this.form;
    }

});