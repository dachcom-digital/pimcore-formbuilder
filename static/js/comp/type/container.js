pimcore.registerNS("Formbuilder.comp.type.container");
Formbuilder.comp.type.container = Class.create(Formbuilder.comp.type.base,{

    type: "container",

    showTranslationTab: false,

    templateStore: [],

    getTypeName: function () {
        return t("container");
    },

    getIconClass: function () {
        return "Formbuilder_icon_container";
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
                        xtype: "combo",
                        name: "template",
                        fieldLabel: t("template"),
                        value: this.datax.template,
                        anchor: "100%",
                        queryDelay: 0,
                        displayField: "label",
                        valueField: "key",
                        queryMode: "local",
                        store: this.templateStore,
                        editable: false,
                        triggerAction: "all",
                        summaryDisplay: true
                    },

                    this.generateAttributeRepeaterField()
                ]

            }]

        });

        return this.form;

    }

});
