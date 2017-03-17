pimcore.registerNS("Formbuilder.comp.type.notice");
Formbuilder.comp.type.notice = Class.create(Formbuilder.comp.type.base,{

    type: "notice",

    getTypeName: function () {
        return t("notice");
    },

    getIconClass: function () {
        return "Formbuilder_icon_notice";
    },

    getForm: function() {

        this.templateStore = Ext.create('Ext.data.Store', {
            fields: [{name: 'label'}, {name: 'key'}],
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: '/plugin/Formbuilder/admin_Settings/get-group-templates'
            }
        });

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
                        fieldLabel: t("content"),
                        name: "content",
                        allowBlank:false,
                        anchor: "100%",
                        value: this.datax.content,
                        enableKeyEvents: true,
                        stripCharsRe: /(<([^>]+)>)/
                    }
                ]

            }]

        });

        return this.form;

    },

    getTranslateForm: function(){

        this.getLanguages();

        this.transForm = new Ext.FormPanel({
            bodyStyle:'padding:10px',
            labelWidth: 150,
            defaultType: 'textfield',
            items: [
                {
                    xtype:'fieldset',
                    title: t('content translation'),
                    collapsible: false,
                    autoHeight:true,
                    defaultType: 'textfield',
                    items:[
                        {
                            xtype: "textfield",
                            name: "originalcontent",
                            fieldLabel: t("original content"),
                            anchor: "100%",
                            value: this.datax.content,
                            disabled: true,
                            stripCharsRe: /(<([^>]+)>)/
                        },

                        this.generateLocaleRepeaterField('content')

                    ]
                }

            ]

        });

        return this.transForm;
    }

});