pimcore.registerNS("Formbuilder.comp.validator.base");
Formbuilder.comp.validator.base = Class.create({

    type: "base",
    apiUrl:"http://framework.zend.com/apidoc/core/_Validate_{name}.html#\Zend_Validate_{name}",
    apiPrefix:"",
    errors:[],
    errorsDef:[],

    initialize: function (treeNode, initData, parent) {

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
        return "Formbuilder_icon_validator";
    },

    initData: function (d) {
        this.valid = true;

        this.datax = {
            name: this.getType(),
            fieldtype: this.getType(),
            isValidator: true,
            translate:new Array()
        };

        if(d){
            try{
                this.datax = d;
                if(!this.datax.translate){
                    this.datax.translate = new Array();
                }
                if(!this.datax.messages){
                    this.datax.messages = new Array();
                }
                this.datax.isValidator = true;
            }
            catch(e){

            }
        }
    },

    getType: function () {
        return this.type;
    },

    getErrorsFS: function(){

        var items = [];
        for (var i=0;i<this.errors.length;i++){
        
            var error = this.errors[i];

            items.push({
                xtype:"fieldset",
                title: t('custom error : ') + error,
                collapsible: false,
                autoHeight:true,
                defaultType: 'textfield',
                items:[{
                    xtype: "textfield",
                    name: "messages." + error,
                    value:this.datax["messages." + error],
                    fieldLabel: t("custom error"),
                    anchor: "100%"
                },
                new Ext.ux.form.SuperField({
                    allowEdit: true,
                    name: error,
                    stripeRows:true,
                    values:this.datax.translate[error],
                    fieldLabel: t("traduction"),
                    items: [
                    {
                        xtype: "combo",
                        name: "locale",
                        fieldLabel: t("Locale"),
                        queryDelay: 0,
                        displayField:"label",
                        valueField: "key",
                        mode: 'local',
                        store: this.localeStore,
                        editable: false,
                        triggerAction: 'all',
                        anchor:"100%",
                        summaryDisplay:true,
                        allowBlank:false
                    },{
                        xtype: "textfield",
                        name: "value",
                        fieldLabel: t("value"),
                        anchor: "100%",
                        summaryDisplay:true,
                        allowBlank:false
                    }
                    ]
                })
                ]
            });
        }

        return items;

    },

    getLayout: function () {

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

    onAfterPopulate: function(){
        return true;
    },

    layoutRendered: function () {
        var form = this.form.getForm();
        //This is for the SuperField bug
        form.items.each(function(item,index,length){
            var name = item.getName();
            if(!(item instanceof Ext.form.DisplayField) && !(item instanceof Ext.ux.form.SuperField)){

                
                if(item.ownerCt.layout != "hbox"){
                    item.setValue(this.datax[name]);
                }
            }
        },this
        );

        this.onAfterPopulate();

    },

    getData: function () {
        return this.datax;
    },

    isValid: function(){
        return this.valid;
    },

    applyData: function () {

        this.valid = this.form.getForm().isValid();
        

        if(this.valid == true){
            this.treeNode.getUI().removeClass("tree_node_error");
        }else{
            this.treeNode.getUI().addClass("tree_node_error");
        }

        var data = {};
        
        this.form.getForm().items.each(function(item,index,length){
            var name = item.getName();
            var bug = name.indexOf("[]");
            if(!(item instanceof Ext.form.DisplayField) && bug==-1){
                
                if(item.ownerCt.layout != "hbox"){
                    data[name]=item.getValue();
                }
            }
        },this
        );

        data.translate = {};
        data.messages = this.errors;

        this.transForm.getForm().items.each(function(item,index,length){
            var name = item.getName();
            var bug = name.indexOf("[]");
            if(!(item instanceof Ext.form.DisplayField) && bug==-1){
                
                if(item instanceof Ext.ux.form.SuperField){
                    data.translate[name]= item.getValue();
                }else{
                    data[name] = item.getValue();
                }
            }
        },this
        );

        //var data = this.form.getForm().getFieldValues();
        data.fieldtype = this.getType();
        
        
        this.datax = data;
            
        this.datax.isValidator = true;
        this.datax.fieldtype = this.getType();
        this.datax.name = this.getType();
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

    getForm: function(){
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

    getLanguages: function(){


        var languages = pimcore.globalmanager.get("Formbuilder.languages");

        var values = new Array();

        for (var i=0;i<languages.length;i++){
            values.push([languages[i],languages[i]]);
        };

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
            items: [this.getErrorsFS()]

        });

        return this.transForm;
    }

});