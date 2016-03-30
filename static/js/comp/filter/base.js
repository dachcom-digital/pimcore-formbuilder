pimcore.registerNS("Formbuilder.comp.filter.base");
Formbuilder.comp.filter.base = Class.create({

    type: "base",
    apiUrl:"http://framework.zend.com/apidoc/core/_Filter_{name}.html#\Zend_Filter_{name}",
    apiPrefix:"",

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

        //var data = this.form.getForm().getFieldValues();
        data.fieldtype = this.getType();
        
        
        this.datax = data;
            
        this.datax.isFilter = true;
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
                    style:{marginBottom : "5px"}
                }
                
            ]
        });
        return this.form;
    }

});