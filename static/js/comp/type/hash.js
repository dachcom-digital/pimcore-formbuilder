pimcore.registerNS("Formbuilder.comp.type.hash");
Formbuilder.comp.type.hash = Class.create(Formbuilder.comp.type.base,{

    type: "hash",

    showTranslationTab: false,

    getTypeName: function () {
        return t("hash");
    },

    getIconClass: function () {
        return "Formbuilder_icon_hash";
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

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "salt",
                fieldLabel: t("Salt"),
                anchor: "100%"
            },
            {
                xtype: "label",
                style:'display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;',
                text: t("We recommend using the salt option for the element - two hashes with same names and different salts would not collide")
            },
            {
                xtype: "numberfield",
                name: "timeout",
                fieldLabel: t("Timeout"),
                allowDecimals:false,
                anchor: "100%"
            }

        ]
        });

        this.form.add(thisNode);

        return this.form;
    }

});