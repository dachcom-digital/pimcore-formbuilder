pimcore.registerNS("Formbuilder.comp.validator.date");
Formbuilder.comp.validator.date = Class.create(Formbuilder.comp.validator.base,{

    type: "date",
    errors:["dateInvalid","dateInvalidDate","dateFalseFormat"],

    initialize: function(treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function() {
        return t("date");
    },   
    
    getIconClass: function() {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super) {

        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "format",
                fieldLabel: t("date format"),
                anchor: "100%",
                value: this.datax.format
            },
            {
                xtype: "textfield",
                name: "locale",
                fieldLabel: t("locale"),
                anchor: "100%",
                value: this.datax.locale
            }
        ]
        });

        this.form.add(thisNode);

        return this.form;
    }
});