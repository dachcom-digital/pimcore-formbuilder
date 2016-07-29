pimcore.registerNS("Formbuilder.comp.validator.extension");
Formbuilder.comp.validator.extension = Class.create(Formbuilder.comp.validator.base,{

    type: "extension",
    apiPrefix:"File_",
    errors:["fileExtensionFalse","fileExtensionNotFound"],

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function() {
        return t("extension");
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
                xtype: "checkbox",
                name: "case",
                fieldLabel: t("case sensitive"),
                checked: this.datax.case
            },{
                xtype: "textfield",
                name: "extension",
                fieldLabel: t("extensions (sep',')"),
                anchor: "100%",
                value: this.datax.extension
            }
        ]
        });

        this.form.add(thisNode);

        return this.form;
    }
});