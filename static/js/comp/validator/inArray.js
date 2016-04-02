pimcore.registerNS("Formbuilder.comp.validator.inArray");
Formbuilder.comp.validator.inArray = Class.create(Formbuilder.comp.validator.base,{

    type: "inArray",
    errors:["notInArray"],

    initialize: function (treeNode, initData) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getTypeName: function () {
        return t("inArray");
    },   
    
    getIconClass: function () {
        return "Formbuilder_icon_validator";
    },

    applyData: function($super) {

        $super();

        var _self = this,
            inArrayCouples = {};

        var formItems = this.form.queryBy(function() {
            return true;
        });

        for (var i = 0; i < formItems.length; i++) {

            var item = formItems[i];

            if (typeof item.getValue == "function") {

                var val = item.getValue(),
                    name = item.getName();

                if (name.substring(0, 9) == "haystack_") {

                    if( val !== "") {

                        var elements = name.split('_');

                        if( !inArrayCouples[elements[2]] ) {
                            inArrayCouples[elements[2]] = {'name' : null, 'value' : null}
                        }

                        inArrayCouples[ elements[2] ][ elements[1] ] = val;

                    }

                }

            }

        }

        if( Object.keys(inArrayCouples).length > 0) {

            this.datax["haystack"] = [];
            if( Object.keys(inArrayCouples).length > 0) {
                Ext.Object.each(inArrayCouples, function (name, value) {
                    _self.datax["haystack"].push( value );
                });
            }
        }

    },

    getForm: function($super){

        $super();

        var thisNode = new Ext.form.FieldSet({

            title: t("This node"),
            collapsible: true,
            defaultType: 'textfield',
            items:[{
                xtype: "checkbox",
                name: "strict",
                fieldLabel: t("Strict"),
                checked:false
                },

                this.generateInArrayRepeater()

            ]
        });

        this.form.add(thisNode);
        return this.form;
    },

    generateInArrayRepeater: function( ) {

        var selector = null;

        var addMetaData = function (name, value) {

            if(typeof name != "string") {
                name = "";
            }
            if(typeof value != "string") {
                value = "";
            }

            var count = selector.query("button").length+1;

            var combolisteners = {
                "afterrender": function (el) {
                    el.getEl().parent().applyStyles({
                        float: "left",
                        "margin-right": "5px"
                    });
                }
            };

            var items = [{
                xtype: "textfield",
                name: "haystack_name_" + count,
                fieldLabel: t("Key"),
                anchor: "100%",
                summaryDisplay: true,
                allowBlank: false,
                value : name,
                flex: 1,
                listeners: combolisteners
            },
            {
                xtype: "textfield",
                name: "haystack_value_" + count,
                fieldLabel: t("Value"),
                anchor: "100%",
                summaryDisplay: true,
                allowBlank: false,
                value : value,
                flex: 1,
                listeners: combolisteners
            }
            ];

            var compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: "padding-bottom:5px;",
                items: items
            });

            compositeField.add([{
                xtype: "button",
                iconCls: "pimcore_icon_delete",
                style: "float:left;",
                handler: function (compositeField, el) {
                    selector.remove(compositeField);
                    selector.updateLayout();
                }.bind(this, compositeField)
            },{
                xtype: "box",
                style: "clear:both;"
            }]);

            selector.add(compositeField);
            selector.updateLayout();

        }.bind(this);

        selector = new Ext.form.FieldSet({

            title: t("haystack"),
            collapsible: false,
            autoHeight:true,
            width: 700,
            style: "margin-top: 20px;",
            items: [{
                xtype: "toolbar",
                style: "margin-bottom: 10px;",
                items: ["->", {
                    xtype: 'button',
                    text: t("add"),
                    iconCls: "pimcore_icon_add",
                    handler: addMetaData,
                    tooltip: {
                        title:'',
                        text: t('add_metadata')
                    }
                }]
            }]
        });

        try {

            if(typeof this.datax.haystack == "object" && this.datax.haystack.length > 0) {

                this.datax.haystack.forEach(function(field) {
                    addMetaData(field["name"], field["value"]);
                });

            }

        } catch (e) {}

        return selector;

    }

});