pimcore.registerNS("Formbuilder.comp.validator.callback");
Formbuilder.comp.validator.callback = Class.create(Formbuilder.comp.validator.base,{

    type: "callback",
    errors:["callbackInvalid","callbackValue"],

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("callback");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    getForm: function($super){
        $super();
        var thisNode = new Ext.form.FieldSet({
            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "textfield",
                name: "callback.0",
                fieldLabel: t("class name"),
                anchor: "100%"
            },
            {
                xtype: "textfield",
                name: "callback.1",
                fieldLabel: t("static function name"),
                anchor: "100%"
            }

        ]
        });
        this.form.add(thisNode);
        return this.form;
    }

});