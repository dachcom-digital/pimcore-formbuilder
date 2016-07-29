pimcore.registerNS("Formbuilder.comp.validator.stringLength");
Formbuilder.comp.validator.stringLength = Class.create(Formbuilder.comp.validator.base,{

    type: "stringLength",
    errors:["stringLengthInvalid","stringLengthTooShort","stringLengthTooLong"],

    initialize: function (treeNode, initData, parent) {
        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function() {
        return t("stringLength");
    },   
    
    getIconClass: function() {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){

        $super();

        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "numberfield",
                name: "min",
                fieldLabel: t("Min value"),
                allowDecimals : true,
                anchor: "100%",
                value: this.datax.min
            },
            {
                xtype: "numberfield",
                name: "max",
                fieldLabel: t("Max value"),
                allowDecimals : true,
                anchor: "100%",
                value: this.datax.max
            }
        ]
        });

        this.form.add(thisNode);

        return this.form;
    }

});