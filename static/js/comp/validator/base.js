pimcore.registerNS("Formbuilder.comp.validator.base");
Formbuilder.comp.validator.base = Class.create({

    type: "base",
    apiUrl:"http://apigen.juzna.cz/doc/zendframework/zf1/class-Zend_Validate_{name}.html",
    apiPrefix:"",
    errors:[],
    errorsDef:[],

    initialize: function(treeNode, initData) {

        this.treeNode = treeNode;
        this.initData(initData);
    },
    
    getApiUrl: function(){

        var name = this.getType();
        var firstLetter = name.substr(0, 1);
        name =  firstLetter.toUpperCase() + name.substr(1);
        name = this.apiPrefix + name;

        var url = str_replace("{name}", name, this.apiUrl);
        return url;

    },
    
    viewApi: function(){

        var wind = new Formbuilder.apiwindow(this.getApiUrl());
        wind.showWindow();

    },

    getTypeName: function() {

        return t("base");

    },

    getIconClass: function() {

        return "Formbuilder_icon_validator";

    },

    initData: function (d) {

        this.valid = true;

        this.datax = {
            name: this.getType(),
            fieldtype: this.getType(),
            isValidator: true,
            translate: []
        };

        if(d){

            try{

                this.datax = d;
                if(!this.datax.translate){
                    this.datax.translate = [];
                }
                if(!this.datax.messages){
                    this.datax.messages = [];
                }
                this.datax.isValidator = true;
            }
            catch(e){

            }
        }
    },

    getType: function() {
        return this.type;
    },

    getLayout: function() {

        this.getLanguages();

        this.layout = new Ext.Panel({
            title: t("Field type ") + this.getTypeName(),
            closable:false,
            autoScroll:true,
            items: [this.getForm()]

        });

        this.layout2 = new Ext.Panel({
            title: t("custom error panel"),
            closable:false,
            autoScroll:true,
            listeners: {
                activate: function(tab){
                    this.applyData();
                    this.layout2.removeAll();
                    this.layout2.add(this.getTranslatForm());
                }.bind(this)
            },
            items: [this.getTranslatForm()]

        });

        this.tab = new Ext.TabPanel({
            tabPosition: "top",
            region:'center',
            deferredRender:true,
            enableTabScroll:true,
            border: false,
            items: [this.layout,this.layout2],
            activeTab: 0
        });

        this.layout.on("render", this.layoutRendered.bind(this));

        return this.tab;
    },

    layoutRendered: function() {

    },

    getData: function() {
        return this.datax;
    },

    isValid: function(){
        return this.valid;
    },

    applyData: function() {

        var data = {};

        var formItems = this.form.queryBy(function() {
            return true;
        });

        for (var i = 0; i < formItems.length; i++) {

            var item = formItems[i];

            if (typeof item.getValue == "function") {

                var val = item.getValue(),
                    name = item.getName();

                if(item.ownerCt.layout != "hbox") {
                    data[name] = val;
                }
            }

        }

        data.translate = {};
        data.messages = this.errors;
        var translateCouples = {};

        var formTransItems = this.transForm.queryBy(function() {
            return true;
        });

        for (var i = 0; i < formTransItems.length; i++) {

            var item = formTransItems[i];

            if (typeof item.getValue == "function") {

                var val = item.getValue(),
                    name = item.getName();

                if (name.substring(0, 10) == "translate_") {

                    if( val !== "") {

                        var elements = name.split('_');

                        var translateType = elements[1],
                            type = elements[2],
                            id = elements[3];

                        //define translate type
                        if( !translateCouples[translateType] ) {
                            translateCouples[translateType] = {}
                        }

                        //define translate name
                        if( !translateCouples[ translateType ][ id ] ) {
                            translateCouples[ translateType ][ id ] = {'name' : null, 'value' : null}
                        }

                        //set data
                        translateCouples[ translateType ][ id ][ type ] = val;

                    }

                } else {

                    if(item.ownerCt.layout != "hbox") {
                        data.translate[name] = val;
                    }

                }

            }

        }

        if( Object.keys(translateCouples).length > 0) {

            //each type
            Ext.Object.each(translateCouples, function (name, translateValues) {

                //each object
                data["translate"][name] = [];
                if( Object.keys(translateValues).length > 0) {
                    Ext.Object.each(translateValues, function (id, value) {
                        data["translate"][name].push( value );
                    });

                }

            });
        }

        data.fieldtype = this.getType();

        this.datax = data;
            
        this.datax.isValidator = true;
        this.datax.fieldtype = this.getType();
        this.datax.name = this.getType();
    },

    getHookForm: function() {

      var fs = new Ext.form.FieldSet({
            title: t("Hook"),
            collapsible: true,
            collapsed:true,
            defaultType: 'textfield',
            items:[{
                    xtype: "textfield",
                    name: "custom_class",
                    fieldLabel: t("custom class"),
                    anchor: "100%"
                },
                {
                    xtype: "textfield",
                    name: "custom_action",
                    fieldLabel: t("static action"),
                    anchor: "100%"
                }
            ]});
        return fs;
    },

    getForm: function() {

        this.form = new Ext.FormPanel({
            bodyStyle:'padding:5px 5px 0',
            labelWidth: 150,
            defaultType: 'textfield',
            items: [this.getHookForm(),{
                xtype:"button",
                text: t("View API"),
                iconCls: "pimcore_icon_api",
                handler: this.viewApi.bind(this),
                style:{
                    marginBottom : "5px"
                }
            }
                
            ]
        });
        return this.form;
    },

    getLanguages: function() {

        var languages = pimcore.globalmanager.get("Formbuilder.languages");

        var values = new Array();

        for (var i=0;i<languages.length;i++) {
            values.push([languages[i],languages[i]]);
        };

        var store = new Ext.data.ArrayStore({
            fields: ["key","label"],
            data : values
        });

        this.localeStore = store;
        return this.localeStore;

    },

    getTranslatForm: function() {

        this.getLanguages();

        this.transForm = new Ext.FormPanel({
            bodyStyle:'padding:5px 5px 0',
            labelWidth: 150,
            defaultType: 'textfield',

            items: [{
                xtype:'fieldset',
                title: t('label translation'),
                collapsible: false,
                autoHeight:true,
                defaultType: 'textfield',
                items: this.getErrorFieldSet()
            }]

        });

        return this.transForm;
    },

    getErrorFieldSet: function() {

        var items = [];

        for (var i=0;i<this.errors.length;i++) {

            var error = this.errors[i];

            items.push({

                xtype:"fieldset",
                title: t('custom error : ') + error,
                collapsible: false,
                autoHeight:true,
                defaultType: 'textfield',
                items:[
                    {
                        xtype: "textfield",
                        name: "messages." + error,
                        value: this.datax["translate"]["messages." + error],
                        fieldLabel: t("custom error"),
                        anchor: "100%"
                    },

                    this.generateValidateLocateRepeater( error )

                ]
            });
        }

        return items;

    },

    generateValidateLocateRepeater: function( elementName ) {

        var selector = null,
            storeData = this.localeStore;

        var addMetaData = function (name, value, elementName) {

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
                xtype: "combo",
                name: "translate_" + elementName + "_name_" + count,
                fieldLabel: t("Locale"),
                queryDelay: 0,
                displayField: "label",
                valueField: "key",
                mode: 'local',
                store: storeData,
                editable: true,
                triggerAction: 'all',
                anchor: "100%",
                value: name,
                summaryDisplay: true,
                allowBlank: false,
                flex: 1,
                listeners: combolisteners
            },
                {
                xtype: "textfield",
                name: "translate_" + elementName + "_value_" + count,
                fieldLabel: t("value"),
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

            title: t("translation"),
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
                    handler: addMetaData.bind(null, null, null, elementName),
                    tooltip: {
                        title:'',
                        text: t('add_metadata')
                    }
                }]
            }]
        });

        try {

            if(typeof this.datax.translate == "object" && typeof this.datax.translate[elementName] == "object" && this.datax.translate[elementName].length > 0) {

                this.datax.translate[elementName].forEach(function(field) {
                    addMetaData(field["name"], field["value"], elementName);
                });

            }

        } catch (e) {}

        return selector;

    }

});