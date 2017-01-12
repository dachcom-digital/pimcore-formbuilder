pimcore.registerNS("Formbuilder.comp.type.displayGroup");
Formbuilder.comp.type.displayGroup = Class.create(Formbuilder.comp.type.base,{

    type: "displayGroup",

    getTypeName: function () {
        return t("displayGroup");
    },

    getIconClass: function () {
        return "Formbuilder_icon_displayGroup";
    },

    getForm: function() {

        this.form = new Ext.FormPanel({
            bodyStyle:'padding:10px',
            labelWidth: 150,
            defaultType: 'textfield',
            items: [ {
                xtype:'fieldset',
                title: t('base settings'),
                collapsible: true,
                autoHeight:true,
                defaultType: 'textfield',
                items:[
                    {
                        xtype: "textfield",
                        fieldLabel: t("name"),
                        name: "name",
                        allowBlank:false,
                        anchor: "100%",
                        value: this.datax.name,
                        enableKeyEvents: true
                    },
                    {
                        xtype: "textfield",
                        name: "label",
                        fieldLabel: t("label"),
                        value: this.datax.label,
                        anchor: "100%"
                    },
                    {
                        xtype: "textfield",
                        name: "description",
                        fieldLabel: t("description"),
                        value: this.datax.description,
                        anchor: "100%"
                    },
                    {
                        xtype: "textfield",
                        name: "legend",
                        fieldLabel: t("legend"),
                        value: this.datax.legend,
                        anchor: "100%"
                    },

                    this.generateAttributeRepeaterField()
                ]

            }]

        });

        return this.form;

    },

    getTranslateForm: function($super) {

        $super();

        var trans = new Ext.form.FieldSet({
            title: t("legend translation"),
            collapsible: true,
            defaultType: 'textfield',
            items:[
                {
                    xtype: "textfield",
                    name: "originallegend",
                    fieldLabel: t("original legend"),
                    anchor: "100%",
                    value:this.datax.legend,
                    disabled:true
                },

                this.generateLocaleRepeaterField('legend')

            ]
        });

        this.transForm.add(trans);
        return this.transForm;

    }

});
