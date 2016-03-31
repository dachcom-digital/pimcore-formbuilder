pimcore.registerNS("Formbuilder.comp.filter.base");
Formbuilder.comp.filter.base = Class.create({

    type: "base",
    apiUrl:"http://apigen.juzna.cz/doc/zendframework/zf1/class-Zend_Filter_{name}.html",
    apiPrefix:"",

    initialize: function (treeNode, initData, parent) {

        this.treeNode = treeNode;
        this.initData(initData);
    },

    getApiUrl: function() {

        var name = this.getType();
        var firstLetter = name.substr(0, 1);
        name =  firstLetter.toUpperCase() + name.substr(1);
        name = this.apiPrefix + name;

        var url = str_replace("{name}", name, this.apiUrl);
        return url;

    },

    viewApi: function() {

        var wind = new Formbuilder.apiwindow(this.getApiUrl());
        wind.showWindow();

    },

    getTypeName: function () {

        return t("base");

    },

    getIconClass: function () {

        return "Formbuilder_icon_filter";

    },

    initData: function (d) {

        this.valid = true;

        this.datax = {
            name: this.getType(),
            fieldtype: this.getType(),
            isFilter: true
        };

        if(d){

            try{
                this.datax = d;
                this.datax.isFilter = true;
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
            title: t("Filter type ") + this.getTypeName(),
            closable:false,
            items: [this.getForm()]

        });

        this.layout.on("render", this.layoutRendered.bind(this));

        return this.layout;
    },

    layoutRendered: function () {

    },

    getData: function () {
        return this.datax;
    },

    isValid: function(){
        return this.valid;
    },

    applyData: function () {

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

        data.fieldtype = this.getType();

        this.datax = data;

        this.datax.isFilter = true;
        this.datax.fieldtype = this.getType();
        this.datax.name = this.getType();
    },

    getHookForm: function() {

        var fs = new Ext.form.FieldSet({
            title: t("Hook"),
            collapsible: true,
            collapsed: true,
            defaultType: 'textfield',
            items: [
                {
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
            ]
        });

        return fs;

    },

    getForm: function(){

        this.form = new Ext.FormPanel({
            bodyStyle:'padding:5px 5px 0',
            labelWidth: 150,
            defaultType: 'textfield',
            items: [
                this.getHookForm(),
                {
                    xtype:"button",
                    text: t("View API"),
                    iconCls: "pimcore_icon_api",
                    handler: this.viewApi.bind(this),
                    style:{marginBottom : "5px"}
                }

            ]
        });

        return this.form;

    }

});