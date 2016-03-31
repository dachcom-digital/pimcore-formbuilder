pimcore.registerNS("Formbuilder.comp.type.base");
Formbuilder.comp.type.base = Class.create({

    type: "base",

    apiUrl:"http://apigen.juzna.cz/doc/zendframework/zf1/class-Zend_Form_Element_{name}.html",

    apiPrefix:"",

    attributeSelector: null,

    initialize: function (treeNode, initData) {

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

    getTypeName: function () {
        return t("base");
    },

    getIconClass: function () {
        return "Formbuilder_icon_base";
    },
    
    initData: function (d) {

        this.valid = true;

        this.datax = {
            name: t("layout"),
            fieldtype: this.getType()
        };

        if(d){

            try{

                if (d.datatype && d.fieldtype && d.name) {
                    var keys = Object.keys(d);
                    for (var i = 0; i < keys.length; i++) {
                        this.datax[keys[i]] = d[keys[i]];
                    }
                }

                this.datax = d;

                if(!this.datax.translate){
                    this.datax.translate = [];
                }
            }
            catch(e){
                    
            }
        }
    },

    getType: function () {
        return this.type;
    },

    getLayout: function () {

        this.layout = new Ext.Panel({
            title: t("Field type ") + this.getTypeName(),
            closable:false,
            autoScroll:true,
            items: [this.getForm()]

        });

        this.layout2 = new Ext.Panel({
            title: t("Translate : ") + this.getTypeName(),
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

        this.tab.on("render", this.layoutRendered.bind(this));

        return this.tab;
    },

    layoutRendered: function () {

        var items = this.tab.queryBy(function() {
            return true;
        });

        for (var i = 0; i < items.length; i++) {
            if (items[i].name == "name") {
                items[i].on("keyup", this.updateName.bind(this));
                break;
            }
        }

    },

    updateName: function () {

        var items = this.tab.queryBy(function() {
            return true;
        });

        if (this.treeNode) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].name == "name") {
                    this.treeNode.set("text", items[i].getValue());
                    break;
                }
            }
        }
           
    },

    getData: function () {
        return this.datax;
    },

    isValid: function(){

        var data = this.getData();
        data.name = trim(data.name);
        var regresult = data.name.match(/[a-zA-Z][a-zA-Z0-9_]*/);

        if (data.name.length > 1 && regresult == data.name
            && in_array(data.name.toLowerCase(), this.forbiddenNames) == false) {
            return true;
        }

        if(in_array(data.name.toLowerCase(), this.forbiddenNames)==true) {
            this.invalidFieldNames = true;
        }

        return false;

    },

    applyData: function () {

        if (!this.layout) {
            //return;
        }

        //1. fill default values and parse attr and multioptions repeater.
        var data = {};
        var attrCouples = {};
        var multiOptionsCouples = {};

        var formItems = this.form.queryBy(function() {
            return true;
        });

        for (var i = 0; i < formItems.length; i++) {

            if (typeof formItems[i].getValue == "function") {

                var val = formItems[i].getValue(),
                    name = formItems[i].name;

                if (name.substring(0, 7) == "attrib_") {

                    if( val !== "") {

                        var elements = name.split('_');

                        if( !attrCouples[elements[2]] ) {
                            attrCouples[elements[2]] = {'name' : null, 'value' : null}
                        }

                        attrCouples[ elements[2] ][ elements[1] ] = val;

                    }

                } else if (name.substring(0, 13) == "multiOptions_") {

                    if( val !== "") {

                        var elements = name.split('_');

                        if( !multiOptionsCouples[elements[2]] ) {
                            multiOptionsCouples[elements[2]] = {'name' : null, 'value' : null}
                        }

                        multiOptionsCouples[ elements[2] ][ elements[1] ] = val;

                    }

                } else {

                    data[name] = val;

                }

            }

        }

        if( Object.keys(attrCouples).length > 0) {
            data["attrib"] = [];
            Ext.Object.each(attrCouples, function (name, value) {
                data["attrib"].push( value );
            });
        }

        if( Object.keys(multiOptionsCouples).length > 0) {
            data["multiOptions"] = [];
            Ext.Object.each(multiOptionsCouples, function (name, value) {
                data["multiOptions"].push( value );
            });
        }

        //2. check translations repeater
        data.translate = {};
        var translateCouples = {};

        var translationFormItems = this.transForm.queryBy(function() {
            return true;
        });

        for (var i = 0; i < translationFormItems.length; i++) {

            if (typeof translationFormItems[i].getValue == "function") {

                var val = translationFormItems[i].getValue(),
                    name = translationFormItems[i].name;

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
                            translateCouples[ translateType ][ id ] = {'name' : null, 'value' : null, 'multiOption' : null}
                        }

                        //set data
                        translateCouples[ translateType ][ id ][ type ] = val;

                    }

                } else {

                    data.translate[ name ] = val;
                }
            }
        }

        if( Object.keys(translateCouples).length > 0) {

            //each type
            Ext.Object.each(translateCouples, function (name, translateValues) {

                //each object
                if( Object.keys(translateValues).length > 0) {

                    data["translate"][name] = [];
                    Ext.Object.each(translateValues, function (id, value) {
                        data["translate"][name].push( value );
                    });

                }

            });
        }

        data.fieldtype = this.getType();

        this.datax = data;
        this.datax.fieldtype = this.getType();
    },

    getForm: function() {

        this.form = new Ext.form.FormPanel({
            bodyStyle:'padding:5px 5px 0',
            labelWidth: 150,
            defaultType: 'textfield',
            items: [ this.getHookForm() ,{
                xtype:'fieldset',
                title: t('base settings'),
                collapsible: true,
                autoHeight:true,
                defaultType: 'textfield',
                items:[{
                    xtype:"button",
                    text: t("View API"),
                    iconCls: "pimcore_icon_api",
                    handler: this.viewApi.bind(this),
                    style:{marginBottom : "5px"}
                },
                {
                    xtype: "textfield",
                    fieldLabel: t("name"),
                    name: "name",
                    value: this.datax.name,
                    allowBlank:false,
                    anchor: "100%",
                    enableKeyEvents: true
                },
                {
                    id:"fieldlabel",
                    xtype: "textfield",
                    name: "label",
                    value: this.datax.label,
                    fieldLabel: t("label"),
                    anchor: "100%"
                },
                {
                    id:"fielddescription",
                    xtype: "textfield",
                    name: "description",
                    value: this.datax.description,
                    fieldLabel: t("description"),
                    anchor: "100%"
                },

                {
                    id:"fieldallowempty",
                    xtype: "checkbox",
                    name: "allowEmpty",
                    value: this.datax.allowEmpty,
                    fieldLabel: t("allowEmpty"),
                    checked:true
                },
                {
                    id:"fieldrequired",
                    xtype: "checkbox",
                    name: "required",
                    value: this.datax.required,
                    fieldLabel: t("required"),
                    checked:false
                },
                {
                    id:"fieldvalue",
                    xtype: "textfield",
                    name: "value",
                    value: this.datax.value,
                    fieldLabel: t("value"),
                    anchor: "100%"
                },

                this.generateAttributeRepeaterField()

                ]


            }]
        });
        return this.form;
    },

    getHookForm: function(){

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

    getLanguages: function(){

        var languages = pimcore.globalmanager.get("Formbuilder.languages");

        var values = [];

        for (var i=0;i<languages.length;i++){
            values.push([languages[i],languages[i]]);
        }

        var store = new Ext.data.ArrayStore({
            fields: ["key","label"],
            data : values
        });

        this.localeStore = store;
        return this.localeStore;
            
    },
    
    getTranslatForm: function(){

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
                items:[{
                    xtype: "textfield",
                    name: "originallabel",
                    fieldLabel: t("original label"),
                    anchor: "100%",
                    value:this.datax.label,
                    disabled:true
                },

                this.generateLocaleRepeaterField('label')

                ]
            },
            {
                xtype:'fieldset',
                title: t('description translation'),
                collapsible: false,
                autoHeight:true,
                defaultType: 'textfield',
                items:[{
                    xtype: "textfield",
                    name: "originaldescription",
                    fieldLabel: t("original description"),
                    anchor: "100%",
                    value:this.datax.description,
                    disabled:true
                },

                this.generateLocaleRepeaterField('description'),

                ]
            }

            ]

        });
        
        return this.transForm;
    },
    
    checkPath: function(path,field){

        Ext.Ajax.request({
            url: "/plugin/Formbuilder/Settings/checkpath",
            method: "post",
            params: {
                path:path
            },
            success: this.pathChecked.bind(field)
        });

    },

    generateAttributeRepeaterField : function() {

        var html = new Ext.data.ArrayStore({
            fields: ["value","label"],
            data : [["class","class"],["id","id"],["style","style"],["maxlegth","maxlength"],["disabled","disabled"],["readonly","readonly"],["size","size"],["title","title"],["onchange","onchange"],["onclick","onclick"],["ondbclick","ondbclick"],["onfocus","onfocus"],["onkeydown","onkeydown"],["onkeypress","onkeypress"],["onkeyup","onkeyup"],["onmousedown","onmousedown"],["onmousemove","onmousemove"],["onmouseout","onmouseout"],["onmouseover","onmouseover"],["onmouseup","onmouseup"],["onselect","onselect"]]
        });

        var addMetaData = function (name, value) {

            if(typeof name != "string") {
                name = "";
            }
            if(typeof value != "string") {
                value = "";
            }

            var count = this.attributeSelector.query("button").length+1;

            var combolisteners = {
                "afterrender": function (el) {
                    el.getEl().parent().applyStyles({
                        float: "left",
                        "margin-right": "5px"
                    });
                }
            };

            var compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: "padding-bottom:5px;",
                items: [{
                        xtype: "combo",
                        name: "attrib_name_" + count,
                        fieldLabel: t("attribute name"),
                        queryDelay: 0,
                        displayField: "label",
                        valueField: "value",
                        mode: 'local',
                        store: html,
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
                        name: "attrib_value_" + count,
                        fieldLabel: t("attribute value"),
                        anchor: "100%",
                        value: value,
                        summaryDisplay: true,
                        allowBlank: false,
                        flex: 1,
                        listeners: combolisteners
                    }]
            });

            compositeField.add([{
                xtype: "button",
                iconCls: "pimcore_icon_delete",
                style: "float:left;",
                handler: function (compositeField, el) {
                    this.attributeSelector.remove(compositeField);
                    this.attributeSelector.updateLayout();
                }.bind(this, compositeField)
            },{
                xtype: "box",
                style: "clear:both;"
            }]);

            this.attributeSelector.add(compositeField);
            this.attributeSelector.updateLayout();

        }.bind(this);

        this.attributeSelector = new Ext.form.FieldSet({

            title:  t("attribute name") + ' & ' + t("attribute value"),
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

            if(typeof this.datax.attrib == "object" && this.datax.attrib.length > 0) {

                this.datax.attrib.forEach(function(field) {
                    addMetaData(field["name"], field["value"] );
                });
            }

        } catch (e) {}

        return this.attributeSelector;
    },

    generateLocaleRepeaterField : function( elementName ) {

        var selector = null,
            storeData = this.localeStore,
            multiOptionStore = this.multiOptionStore;

        var addMetaData = function (name, value, multiOption, elementName) {

            if(typeof name != "string") {
                name = "";
            }
            if(typeof value != "string") {
                value = "";
            }
            if(typeof multiOption != "string") {
                multiOption = "";
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

            if( elementName == 'multiOptions') {

                items.splice(1, 0, {
                    xtype: "combo",
                    name: "translate_" + elementName + "_multiOption_" + count,
                    fieldLabel: t("single multiOption"),
                    queryDelay: 0,
                    displayField: "label",
                    valueField: "key",
                    mode: 'local',
                    store: multiOptionStore,
                    editable: true,
                    triggerAction: 'all',
                    anchor: "100%",
                    value: multiOption,
                    summaryDisplay: true,
                    allowBlank: false,
                    flex: 1,
                    listeners: combolisteners
                });
            }

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

            title: t(elementName + " translation"),
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
                    handler: addMetaData.bind(null, null, null, null, elementName),
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
                    addMetaData(field["name"], field["value"], field["multiOption"], elementName);
                });

            }

        } catch (e) {}

        return selector;
    },

    generateMultiOptionsRepeaterField : function( ) {

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

            var compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: "padding-bottom:5px;",
                items: [{
                    xtype: "textfield",
                    name: "multiOptions_name_" + count,
                    fieldLabel: t("Option"),
                    anchor: "100%",
                    summaryDisplay: true,
                    allowBlank: false,
                    value : value,
                    flex: 1,
                    listeners: combolisteners
                },
                    {
                        xtype: "textfield",
                        name: "multiOptions_value_" + count,
                        fieldLabel: t("Value"),
                        anchor: "100%",
                        summaryDisplay: true,
                        allowBlank: false,
                        value : value,
                        flex: 1,
                        listeners: combolisteners
                    }]
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

            title: "multiOptions",
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

            if(typeof this.datax.multiOptions == "object" && this.datax.multiOptions.length > 0) {

                this.datax.multiOptions.forEach(function(field) {
                    addMetaData(field["name"], field["value"]);
                });

            }

        } catch (e) {}

        return selector;
    },

    pathChecked: function(response) {

        var ret = Ext.decode(response.responseText);

        if(ret.success == true) {

            this.clearInvalid();

        } else {

            this.markInvalid(t("thePath doesn't exist"));
            
        }

    }

});