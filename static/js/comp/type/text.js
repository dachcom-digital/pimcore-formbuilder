pimcore.registerNS("Formbuilder.comp.type.text");
Formbuilder.comp.type.text = Class.create(Formbuilder.comp.type.base,{

    type: "text",

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

        var descriptionField,
            _me = this,
            inputStore = new Ext.data.ArrayStore(
                {
                    fields: ["value","label"],
                    data : [
                        ["default", t("default")],
                        ["email", t("html5 email")],
                        ["url", t("html5 url")],
                        ["number", t("html5 number")],
                        ["range", t("html5 range")],
                        ["date", t("html5 date")],
                        ["month", t("html5 month")],
                        ["week", t("html5 week")],
                        ["time", t("html5 time")],
                        ["datetime-local", t("html5 datetime")]
                    ]
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
                            select: function(combo,record,index) {

                                var numberAttributes = _me.numberAttributes;

                                //hide
                                numberAttributes.hide();

                                switch(record.data.value) {
                                    case "number" :
                                        numberAttributes.show();
                                }
                            }
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
                    name: "optionsNumber.min",
                    fieldLabel: t("number min"),
                    allowDecimals: false,
                    anchor: "100%",
                    value:this.datax['optionsNumber.min']
                },
                {
                    xtype: "numberfield",
                    name: "optionsNumber.max",
                    fieldLabel: t("number max"),
                    allowDecimals: false,
                    anchor: "100%",
                    value:this.datax['optionsNumber.max']
                },
                {
                    xtype: "numberfield",
                    name: "optionsNumber.step",
                    fieldLabel: t("number step"),
                    allowDecimals: false,
                    anchor: "100%",
                    value:this.datax['optionsNumber.step']
                }
            ]
        });

        this.form.add(thisNode);

        this.form.add( this.numberAttributes );

        return this.form;
    }

});