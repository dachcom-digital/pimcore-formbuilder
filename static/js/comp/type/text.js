pimcore.registerNS("Formbuilder.comp.type.text");
Formbuilder.comp.type.text = Class.create(Formbuilder.comp.type.base,{

    type: "text",

    allowedHtml5Elements: {

        "default" : {
            label : t("default")
        },
        "email" : {
            label : t("html5 email")
        },
        "url" : {
            label : t("html5 url")
        },
        "number" : {
            label : t("html5 number"),
            attributes : [
                "min",
                "max",
                "step"
            ]
        },
        "range" : {
            label : t("html5 range")
        },
        "date" : {
            label: t("html5 date")
        },
        "month" : {
            label : t("html5 month")
        },
        "week" : {
            label : t("html5 week")
        },
        "time" : {
            label : t("html5 time")
        },
        "datetime-local" : {
            label : t("html5 datetime")
        },
    },

    getTypeName: function () {
        return t("text");
    },

    getIconClass: function () {
        return "Formbuilder_icon_text";
    },

    onAfterPopulate: function() {

        var inputType = this.form.getForm().findField("inputType"),
            numberAttributes = this.numberAttributes;

        //first hide all!
        numberAttributes.hide();

        switch( inputType.getValue() ) {

            case "number" :
                numberAttributes.show();
                break;
        }

    },

    getForm: function($super) {

        $super();

        var fields = [];

        Ext.iterate(this.allowedHtml5Elements, function(type, value) {
            fields.push( [type, value.label] );
        });

        var descriptionField,
            _me = this,
            inputStore = new Ext.data.ArrayStore(
                {
                    fields: ["value","label"],
                    data : fields
                }
            ),
            thisNode = new Ext.form.FieldSet({
                title: t("This node"),
                collapsible: true,
                defaultType: 'textfield',
                items:[
                    {
                        xtype: "combo",
                        name: "inputType",
                        fieldLabel: t("input type"),
                        queryDelay: 0,
                        displayField:"label",
                        valueField: "value",
                        mode: "local",
                        store: inputStore,
                        editable: false,
                        triggerAction: 'all',
                        anchor: "100%",
                        value: this.datax.inputType === undefined ? 'default' : this.datax.inputType,
                        allowBlank:false,
                        listeners: {
                            scope:this,
                            change: function(combo,newValue,oldValue,options) {

                                var numberAttributes = _me.numberAttributes;

                                if( newValue !== oldValue ) {

                                    this.resetHtml5Attributes( newValue );

                                    //hide
                                    numberAttributes.hide();

                                    switch( newValue ) {
                                        case "number" :
                                            numberAttributes.show();
                                            break;
                                    }

                                }

                            }.bind(this)
                        }
                    }
                ]
            }
        );

        descriptionField = {
            xtype: "label",
            style:"display:block; padding:5px; margin:0 0 20px 0; background:#f5f5f5;border:1px solid #eee;",
            text: t("Important! Use these attributes only if you have activated the 'html5 form validation option'. otherwise use the element validation and filter settings!")
        };

        this.numberAttributes = new Ext.form.FieldSet({

            title: t("html5 attributes"),
            collapsible: true,
            defaultType: 'textfield',
            items:[

                descriptionField,

                {
                    xtype: "numberfield",
                    name: "html5Options.min",
                    fieldLabel: t("number min"),
                    allowDecimals: false,
                    anchor: "100%",
                    value:this.datax['html5Options.min']
                },
                {
                    xtype: "numberfield",
                    name: "html5Options.max",
                    fieldLabel: t("number max"),
                    allowDecimals: false,
                    anchor: "100%",
                    value:this.datax['html5Options.max']
                },
                {
                    xtype: "numberfield",
                    name: "html5Options.step",
                    fieldLabel: t("number step"),
                    allowDecimals: false,
                    anchor: "100%",
                    value:this.datax['html5Options.step']
                }
            ]
        });

        this.form.add( thisNode );

        this.form.add( this.numberAttributes );

        return this.form;

    },

    resetHtml5Attributes: function( ignoreElement ) {

        var _ = this;

        Ext.iterate(this.allowedHtml5Elements, function(type, value) {

            var attributes = value.attributes ? value.attributes : [];

            if( _.hasOwnProperty( type + "Attributes" ) ) {
                Ext.each(_[type + "Attributes"].query("field"), function(field) {
                    field.setValue(null);
                    field.resetOriginalValue();

                });
            }

            Ext.each(attributes, function(attribute, index) {
                if( _.datax.hasOwnProperty( 'html5Options.' + attribute ) ) {
                    delete _.datax[ 'html5Options.' + attribute ];
                }
            });

        });

    }
});