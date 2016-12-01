pimcore.registerNS("Formbuilder.comp.elem");
Formbuilder.comp.elem = Class.create({

    initialize: function(data, parentPanel) {

        this.parentPanel = parentPanel;
        this.data = data;

        this.copyData = null;
        this.rootFields = [];

        this.addLayout();
        this.initLayoutFields();
    },

    addLayout: function() {

        this.tree = Ext.create('Ext.tree.Panel', {

            region: "west",
            autoScroll: true,
            listeners: this.getTreeNodeListeners(),
            animate:false,
            split: true,
            enableDD: true,
            width: 300,

            root: {
                id: "0",
                text: t("base"),
                iconCls:"Formbuilder_icon_root",
                isTarget: true,
                leaf:true,
                root: true

            },
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    ddGroup: "element"
                }
            }
        });

        this.tree.on("nodedragover", this.onTreeNodeOver.bind(this));

        this.editPanel = new Ext.Panel({
            region: "center",
            bodyStyle: "padding: 10px;",
            autoScroll: true
        });

        this.panel = new Ext.Panel({
            title: this.data.name + " ( ID: " + this.data.id + ")",
            id: this.data.id,
            closable: true,
            iconCls: "Formbuilder_icon_root",
            autoScroll: true,
            buttons: [
                {
                    text: t("import"),
                    iconCls: "pimcore_icon_import",
                    handler: this.showImportPanel.bind(this)
                },
                {
                    text: t("export"),
                    iconCls: "pimcore_icon_export",
                    handler: this.getExportFile.bind(this)
                },
                {
                    text: t("save"),
                    iconCls: "pimcore_icon_save",
                    handler: this.save.bind(this)
                }
            ],
            border: false,
            layout: "border",
            items: [this.tree, this.editPanel]

        });

        this.panel.on("beforedestroy", function() {

            if( this.data.id && this.parentPanel.panels["form_" + this.data.id] ) {
                this.editPanel.removeAll();
                delete this.parentPanel.panels["form_" + this.data.id];
            }

            if( this.parentPanel.tree.initialConfig !== null &&
                Object.keys( this.parentPanel.panels ).length === 0 ) {

                this.parentPanel.tree.getSelectionModel().deselectAll();
            }

        }.bind(this));

        this.parentPanel.getEditPanel().add(this.panel);
        this.editPanel.add(this.getRootPanel());

        this.setCurrentNode("root");
        this.parentPanel.getEditPanel().setActiveTab(this.panel);

        pimcore.layout.refresh();

    },

    activate: function() {

        this.parentPanel.getEditPanel().setActiveTab(this.panel);

    },

    initLayoutFields: function() {

        if (this.data.mainDefinitions) {
            if (this.data.mainDefinitions.childs) {
                for (var i = 0; i < this.data.mainDefinitions.childs.length; i++) {
                    this.tree.getRootNode().appendChild(this.recursiveAddNode(this.data.mainDefinitions.childs[i],
                        this.tree.getRootNode()));
                }
                this.tree.getRootNode().expand();
            }
        }

    },

    recursiveAddNode: function(con, scope) {

        var stype = null, fn = null, newNode = null;

        if( con.isFilter == true) {
            stype = "filter";
        } else if( con.isValidator == true) {
            stype = "validator";
        }

        fn = this.addElemChild.bind(scope, con.fieldtype, con, stype);
        newNode = fn();

        if (con.childs) {
            for (var i = 0; i < con.childs.length; i++) {
                this.recursiveAddNode(con.childs[i], newNode);
            }
        }

        return newNode;
    },

    getTreeNodeListeners: function() {

        var listeners = {

            'itemclick' : this.onTreeNodeClick.bind(this),
            'itemcontextmenu': this.onTreeNodeContextmenu.bind(this),
            'beforeitemmove': this.onTreeNodeBeforeMove.bind(this)
        };

        return listeners;

    },
    
    onTreeNodeOver: function(event) {

        var parent = "";

        if (event.point != "append"){
            parent = event.target.parentNode.data.iconCls;
        }else{
            parent = event.target.attributes.iconCls;
        }
        
        switch (parent){
            
            case "Formbuilder_icon_validator" :
                if(event.point != "append" && (event.dropNode.data.iconCls != "Formbuilder_icon_validator" || event.dropNode.data.iconCls != "Formbuilder_icon_filter")){
                    return true;
                }else{
                    return false;
                }
                break;
            case "Formbuilder_icon_filter" :
                if(event.point != "append" && (event.dropNode.data.iconCls != "Formbuilder_icon_validator" || event.dropNode.data.iconCls != "Formbuilder_icon_filter")){
                    return true;
                }else{
                    return false;
                }
                break;
            case "Formbuilder_icon_displayGroup":
                if(event.dropNode.data.iconCls != "Formbuilder_icon_validator" && event.dropNode.data.iconCls != "Formbuilder_icon_filter" && event.dropNode.data.iconCls != "Formbuilder_icon_displayGroup"){
                    return true;
                }else{
                    return false;
                }
                break;
            case "Formbuilder_icon_root" :
                if(event.dropNode.data.iconCls != "Formbuilder_icon_validator" && event.dropNode.data.iconCls != "Formbuilder_icon_filter"){
                    return true;
                }else{
                    return false;
                }
                break;
            default://field
                if(event.dropNode.data.iconCls != "Formbuilder_icon_validator" || event.dropNode.data.iconCls != "Formbuilder_icon_filter"){
                    return true;
                }else{
                    return false;
                }
                break;

        }
    },

    onTreeNodeBeforeMove : function(node, oldParent, newParent, index, eOpts){
        
        switch (newParent.data.iconCls){
            
            case "Formbuilder_icon_validator" :
                return false;
                break;
            case "Formbuilder_icon_filter" :
                return false;
                break;
            case "Formbuilder_icon_displayGroup":
                if(node.data.iconCls != "Formbuilder_icon_validator" && node.data.iconCls != "Formbuilder_icon_filter" && node.data.iconCls != "Formbuilder_icon_displayGroup"){
                    return true;
                }else{
                    return false;
                }
                break;
            case "Formbuilder_icon_root" :
                if(node.data.iconCls != "Formbuilder_icon_validator" && node.data.iconCls != "Formbuilder_icon_filter"){
                    return true;
                }else{
                    return false;
                }
                break;
            default://field
                if(node.data.iconCls != "Formbuilder_icon_validator" || node.data.iconCls != "Formbuilder_icon_filter"){
                    return true;
                }else{
                    return false;
                }
                break;
                    
        }
        
        return false;
    },

    onTreeNodeClick: function(tree, record, item, index, e, eOpts) {

        try {
            this.saveCurrentNode();
        } catch (e) {
            console.log(e);
        }

        this.editPanel.removeAll();

        if (record.data.object) {

            if (record.data.object.datax.locked) {
                return;
            }

            this.editPanel.add(record.data.object.getLayout());
            this.setCurrentNode(record.data.object);
        }

        if (record.data.root) {

            this.editPanel.add(this.getRootPanel());
            this.setCurrentNode("root");
        }

        this.editPanel.updateLayout();

    },

    onTreeNodeContextmenu: function(tree, record, item, index, e, eOpts) {

        e.stopEvent();
        tree.select();

        var menu = new Ext.menu.Menu();

        // specify which childs a layout can have
        // the child-type "data" is a placehoder for all data components
        var allowedTypes = {
            root: ["button","captcha","checkbox","file","hash","hidden","image","download","multiCheckbox","multiselect","password","radio","reset","select","submit","text","textarea"],
            displayGroup: ["button","captcha","checkbox","file","hash","hidden","image","download","multiCheckbox","multiselect","password","radio","reset","select","submit","text","textarea"]
        };

        var allowedFilters = {
            button: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            captcha: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            checkbox: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            file: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            hash: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            hidden: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            image: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            multiCheckbox: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            multiselect: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            password: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            radio: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            reset: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            select: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            submit: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            text: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"],
            textarea: ["alnum","alpha","baseName","boolean","callback","digits","dir","htmlEntities","int","pregReplace","stringToLower","stringToUpper","stringTrim","stripNewlines","stripTags"]
        };

        var allowedValidators = {
            button: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            captcha: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            checkbox: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            file: ["extension","callback"],
            hash: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            hidden: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            image: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            multiCheckbox: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            multiselect: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            password: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            radio: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            reset: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            select: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            submit: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            text: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"],
            textarea: ["alnum","alpha","between","callback","creditCard","date","digits","emailAddress","float","greaterThan","hex","hostname","iban","identical","inArray","int","ip","isbn","lessThan","postCode","regex","stringLength"]
        };


        var parentType = "root";

        if (record.data.object) {
            parentType = record.data.object.type;
        }

        var layoutElem = [];
        var layouts = Object.keys(Formbuilder.comp.type);

        for (var i = 0; i < layouts.length; i++) {
            if (layouts[i] != "layout") {
                if (in_array(layouts[i], allowedTypes[parentType])) {

                    layoutElem.push({
                        text: Formbuilder.comp.type[layouts[i]].prototype.getTypeName(),
                        iconCls: Formbuilder.comp.type[layouts[i]].prototype.getIconClass(),
                        handler: this.addElemChild.bind(record, layouts[i])
                    });
                }

            }
        }

        if (parentType == "root") {
            menu.add(new Ext.menu.Item({
                text: t('Add displayGroup'),
                iconCls: "Formbuilder_icon_displayGroup_add",
                handler: this.addElemChild.bind(record, "displayGroup")
            }));
        }

        if (layoutElem.length > 0) {
            menu.add(new Ext.menu.Item({
                text: t('Add elem item'),
                iconCls: "Formbuilder_icon_item_add",
                hideOnClick: false,
                menu: layoutElem
            }));
        }

        var filterElem = [];
        var filters = Object.keys(Formbuilder.comp.filter);

        for (var i = 0; i < filters.length; i++) {
            if (filters[i] != "layout") {
                if (in_array(filters[i], allowedFilters[parentType])) {
                    filterElem.push({
                        text: Formbuilder.comp.filter[filters[i]].prototype.getTypeName(),
                        iconCls: Formbuilder.comp.filter[filters[i]].prototype.getIconClass(),
                        handler: this.addElemChild.bind(record, filters[i], null, "filter")
                    });
                }

            }
        }

        if (filterElem.length > 0) {
            menu.add(new Ext.menu.Item({
                text: t('Add elem filter'),
                iconCls: "Formbuilder_icon_filter_add",
                hideOnClick: false,
                menu: filterElem
            }));
        }

        var validatorElem = [];
        var validators = Object.keys(Formbuilder.comp.validator);

        for (var i = 0; i < validators.length; i++) {
            if (validators[i] != "layout") {
                if (in_array(validators[i], allowedValidators[parentType])) {
                    validatorElem.push({
                        text: Formbuilder.comp.validator[validators[i]].prototype.getTypeName(),
                        iconCls: Formbuilder.comp.validator[validators[i]].prototype.getIconClass(),
                        handler: this.addElemChild.bind(record, validators[i], null, "validator")
                    });
                }

            }
        }

        if (validatorElem.length > 0) {
            menu.add(new Ext.menu.Item({
                text: t('Add elem validator'),
                iconCls: "Formbuilder_icon_validator_add",
                hideOnClick: false,
                menu: validatorElem
            }));
        }

        var deleteAllowed = true;

        if (record.data.object) {
            if (record.data.object.datax.locked) {
                deleteAllowed = false;
            }
        }

        if (record.id != 0) {

            menu.add(new Ext.menu.Item({
                text: t('copy'),
                iconCls: "pimcore_icon_copy",
                hideOnClick: true,
                handler: this.copyChild.bind(this, tree, record)
            }));
        }

        var showPaste = false;

        if (this.copyData != null) {

            var copyType = this.copyData.data.type;

            if(record.id == 0) {

                if(copyType == "displayGroup") {
                    showPaste = true;
                }

                if(in_array(copyType, allowedTypes[parentType])){
                    showPaste = true;
                }

            } else {

                if( ! (record.data.object.datax.isFilter
                    || record.data.object.datax.isValidator)) {

                    if(in_array(copyType, allowedTypes[parentType])){
                        showPaste = true;
                    }
                    if(in_array(copyType, allowedFilters[parentType])){
                        showPaste = true;
                    }
                    if(in_array(copyType, allowedValidators[parentType])){
                        showPaste = true;
                    }

                }
            }

        }

        if(showPaste == true) {

            menu.add(new Ext.menu.Item({
                text: t('paste'),
                iconCls: "pimcore_icon_paste",
                handler: this.pasteChild.bind(this, tree, record)
            }));

        }

        if (this.id != 0 && deleteAllowed) {

            menu.add(new Ext.menu.Item({
                text: t('delete'),
                iconCls: "pimcore_icon_delete",
                handler: this.removeChild.bind(this, tree, record)
            }));

        }

        menu.showAt(e.pageX, e.pageY);
    },

    setCurrentNode: function(cn) {

        this.currentNode = cn;
    },

    saveCurrentNode: function() {

        var _self = this;

        if (this.currentNode) {

            if (this.currentNode != "root") {

                this.currentNode.applyData();

            } else {

                // save root node data
                var items = this.rootPanel.queryBy(function() {
                    return true;
                });

                var attrCouples = {};
                for (var i = 0; i < items.length; i++) {
                    if (typeof items[i].getValue == "function") {

                        var val = items[i].getValue(),
                            name = items[i].name;

                        if (name.substring(0, 7) == "attrib_") {

                            if( val !== "") {

                                var elements = name.split('_');

                                if( !attrCouples[elements[2]] ) {
                                    attrCouples[elements[2]] = {'name' : null, 'value' : null}
                                }

                                attrCouples[ elements[2] ][ elements[1] ] = val;

                            }


                        } else {
                            this.data[name] = val;
                        }

                    }
                }

                _self.data["attrib"] = [];
                if( Object.keys(attrCouples).length > 0) {
                    Ext.Object.each(attrCouples, function(name, value) {
                        _self.data["attrib"].push( value );
                    });
                }

            }
        }
    },

    getRootPanel: function() {

        var methodStore = new Ext.data.ArrayStore({

            fields: ["value","label"],
            data : [["post","POST"],["get","GET"]]

        });
        
        var html = new Ext.data.ArrayStore({

            fields: ["value","label"],
            data : [["class","class"],["id","id"],["title","title"],["onclick","onclick"],["ondbclick","ondbclick"],["onkeydown","onkeydown"],["onkeypress","onkeypress"],["onkeyup","onkeyup"],["onmousedown","onmousedown"],["onmousemove","onmousemove"],["onmouseout","onmouseout"],["onmouseover","onmouseover"],["onmouseup","onmouseup"],["onselect","onselect"],["onreset","onreset"],["onsubmit","onsubmit"]]

        });
        
        var encStore = new Ext.data.ArrayStore({

            fields: ["value","label"],
            data : [["text/plain","text/plain"],["application/x-www-form-urlencoded","application/x-www-form-urlencoded"],["multipart/form-data","multipart/form-data"]]

        });

        // meta-data
        var addMetaData = function(name, value) {

            if(typeof name != "string") {
                name = "";
            }
            if(typeof value != "string") {
                value = "";
            }

            var count = this.metaDataPanel.query("button").length+1;

            var combolisteners = {
                "afterrender": function(el) {
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
                        summaryDisplay: true,
                        allowBlank: false,
                        value: value,
                        flex: 1,
                        listeners: combolisteners
                    }]
            });

            compositeField.add([{
                xtype: "button",
                iconCls: "pimcore_icon_delete",
                style: "float:left;",
                handler: function(compositeField, el) {
                    this.metaDataPanel.remove(compositeField);
                    this.metaDataPanel.updateLayout();
                }.bind(this, compositeField)
            },{
                xtype: "box",
                style: "clear:both;"
            }]);

            this.metaDataPanel.add(compositeField);
            this.metaDataPanel.updateLayout();

        }.bind(this);

        this.metaDataPanel = new Ext.form.FieldSet({

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

            if(typeof this.data.attrib == "object" && this.data.attrib.length > 0) {

                this.data.attrib.forEach(function(field) {
                    addMetaData(field["name"], field["value"] );
                });

            }

        } catch (e) {}

        this.rootPanel = new Ext.form.FormPanel({

            title: t("Form configuration"),
            bodyStyle: "padding:10px",
            border: false,
            items: [
                {
                    xtype: "textfield",
                    fieldLabel: t("name"),
                    name: "name",
                    width: 300,
                    value: this.data.name
                },
                {
                    xtype: "textfield",
                    name: "action",
                    value: this.data.action ? this.data.action : "/",
                    fieldLabel: t("Action"),
                    width: 300,
                    allowBlank: false
                },
                {
                    xtype: "combo",
                    name: "method",
                    fieldLabel: t("Method"),
                    queryDelay: 0,
                    displayField:"label",
                    valueField: "value",
                    mode: 'local',
                    store: methodStore,
                    editable: true,
                    triggerAction: 'all',
                    width: 300,
                    value: this.data.method ? this.data.method : 'POST',
                    allowBlank: false
                },
                {
                    xtype: "combo",
                    name: "enctype",
                    fieldLabel: t("Enctype"),
                    queryDelay: 0,
                    displayField:"label",
                    valueField: "value",
                    mode: 'local',
                    store: encStore,
                    editable: false,
                    triggerAction: 'all',
                    width: 300,
                    value: this.data.enctype ? this.data.enctype : 'multipart/form-data',
                    allowBlank: false
                },
                {
                    xtype: "checkbox",
                    name: "useAjax",
                    fieldLabel: t("Use Ajax to Submit"),
                    checked: this.data.useAjax === undefined ? true : false,
                    value: this.data.useAjax
                },

                this.metaDataPanel

            ]
        });

        this.rootFields = this.rootPanel.getForm().getFields();

        return this.rootPanel;
    },

    addElemChild: function(type, initData, stype) {

        var nodeLabel = t(type);
        var filter = false;
        var validator = false;

        if (initData) {

            if (initData.name) {
                nodeLabel = initData.name;
            }

        }

        if(stype === "filter" ) {
            filter = true;
        }

        if(stype === "validator") {
            validator = true;
        }

        var newNode = null;

        if(filter === true) {

            newNode = {
                type: "layout",
                draggable: true,
                iconCls: "Formbuilder_icon_filter",
                text: nodeLabel,
                leaf: false,
                expandable: false,
                expanded: true
            };

            newNode = this.appendChild(newNode);
            newNode.set('object', new Formbuilder.comp.filter[type](newNode, initData, this) );

        } else if(validator === true) {

            newNode = {
                type: "layout",
                draggable: true,
                iconCls: "Formbuilder_icon_validator",
                text: nodeLabel,
                leaf: false,
                expandable: false,
                expanded: true
            };

            newNode = this.appendChild(newNode);
            newNode.set('object', new Formbuilder.comp.validator[type](newNode, initData, this) );

        } else {

            newNode = {
                text: nodeLabel,
                type: "layout",
                draggable: true,
                iconCls: "Formbuilder_icon_" + type,
                leaf: false,
                expandable: false,
                expanded: true
            };

            newNode = this.appendChild(newNode);
            newNode.set('object', new Formbuilder.comp.type[type](newNode, initData, this) );

        }

        this.expand();

        return newNode;
    },

    copyChild: function(tree, record) {

        this.copyData = {};

        var newNode = this.cloneChild(tree, record);
        this.copyData = newNode;

        /*
        if (record.id != 0) {

            this.names = [];
            this.saveCurrentNode();
            this.getData();
            this.copyData = record.data.object.datax;

        }
        */
    },

    pasteChild: function(tree, record) {

        var node = this.copyData;
        var newNode = this.cloneChild(tree, node);

        record.appendChild(newNode);
        tree.updateLayout();

        //this.recursiveAddNode.bind(this.copyData, record);

    },

    removeChild: function(tree, record) {

        if (this.id != 0) {
            if (this.currentNode == record.data.object) {
                this.currentNode = null;
                var f = this.onTreeNodeClick.bind(this, this.tree, this.tree.getRootNode());
                f();
            }
            record.remove();
        }
    },

    cloneChild: function(tree, node) {

        var theReference = this;
        var nodeLabel = node.data.text;
        var nodeType = node.data.object.type;

        var config = {
            text: nodeLabel,
            type: nodeType,
            leaf: false,
            expandable: false,
            expanded: true
        };

        config.listeners = theReference.getTreeNodeListeners();

        if (node.data.object) {
            config.iconCls = node.data.object.getIconClass();
        }

        var newNode = node.createNode(config);

        var theData = {};

        if (node.data.object) {
            theData = Ext.apply(theData, node.data.object.datax);
        }

        var newObjectClass = null;

        if( node.data.object.datax.isValidator === true) {
            newObjectClass = Formbuilder.comp.validator[nodeType];
        } else if( node.data.object.datax.isFilter === true) {
            newObjectClass = Formbuilder.comp.filter[nodeType];
        } else {
            newObjectClass = Formbuilder.comp.type[nodeType];
        }

        newNode.data.object = new newObjectClass(newNode, theData);

        var len = node.childNodes ? node.childNodes.length : 0;

        var i = 0;

        // Move child nodes across to the copy if required
        for (i = 0; i < len; i++) {
            var childNode = node.childNodes[i];
            var clonedChildNode = this.cloneChild(tree, childNode);
            newNode.appendChild(clonedChildNode);
        }

        return newNode;

    },

    getNodeData: function(node) {

        var data = {};

        if(node.data.object) {

            if (typeof node.data.object.getData == "function") {

                data = node.data.object.getData();

                data.name = trim(data.name);

                var fieldValidation = true;
                if(typeof node.data.object.isValid == "function") {
                    fieldValidation = node.data.object.isValid();
                }

                var view = this.tree.getView();
                var nodeEl = Ext.fly(view.getNodeByRecord(node));

                var nsName = data.name;
                if( data.isFilter === true ) {
                    nsName = 'f.' + nsName;
                }
                if( data.isValidator === true ) {
                    nsName = 'v.' + nsName;
                }

                if(fieldValidation && (in_array(nsName, this.usedFieldNames) == false)) {

                    this.usedFieldNames.push(nsName);

                    if(nodeEl) {
                        nodeEl.removeCls("tree_node_error");
                    }

                } else {

                    if(nodeEl) {
                        nodeEl.addCls("tree_node_error");
                    }

                    Ext.Msg.alert(t("error"), t("problem_creating_new_elem"));

                    this.getDataSuccess = false;
                    return false;
                }
            }
        }

        data.childs = null;

        if (node.childNodes.length > 0) {

            data.childs = [];

            for (var i = 0; i < node.childNodes.length; i++) {
                data.childs.push(this.getNodeData(node.childNodes[i]));
            }

        }

        return data;
    },

    getData: function() {

        this.getDataSuccess = true;
        this.usedFieldNames = [];

        return this.getNodeData( this.tree.getRootNode() );

    },

    showImportPanel: function() {

        var importPanel = new Formbuilder.comp.importer(this);
        importPanel.showPanel();

    },

    importation: function(data) {

        this.parentPanel.getEditPanel().removeAll();
        this.data = array_merge(this.data,data);

        this.addLayout();
        this.initLayoutFields();
    },
    
    getExportFile: function() {

        location.href = "/plugin/Formbuilder/admin_Settings/get-export-file?id=" + this.data.id + "&name=" + this.data.name;

    },

    rootFormIsValid : function(data) {

        var isValid = true;

        if( this.rootFields.length > 0 )
        {
            this.rootFields.each(function(field)
            {
                if( typeof field.getValue == "function" )
                {
                    try
                    {
                        if( field.getValue() === "")
                        {
                            isValid = false;
                            return false;
                        }

                    } catch(e) {

                        //console.warn(e);
                    }

                }
            });
        }

        var regresult = data["name"].match(/[a-zA-Z]+/);

        if( data["name"].length <= 2 || regresult != data["name"] || in_array(data["name"].toLowerCase(), this.parentPanel.forbiddennames)) {
            isValid = false;
        }

        return isValid;

    },

    save: function(ev) {

        this.saveCurrentNode();

        if( this.rootFormIsValid(this.data) ) {

            this.tree.getRootNode().set("cls", "");

            var m = Ext.encode(this.getData()),
                n = Ext.encode(this.data);

            if( this.getDataSuccess ) {

                Ext.Ajax.request({
                    url: "/plugin/Formbuilder/admin_Settings/save",
                    method: "post",
                    params: {
                        configuration: m,
                        values: n,
                        id: this.data.id
                    },
                    success: this.saveOnComplete.bind(this),
                    failure: this.saveOnError.bind(this)
                });

            } else {

                Ext.Msg.alert(t('error'), t('problem_creating_new_elem'));
            }

        } else {

            this.tree.getRootNode().set("cls", "tree_node_error");
            Ext.Msg.alert(t('error'), t('problem_creating_new_elem_form'));

        }

    },

    saveOnComplete: function(response) {

        var res = Ext.decode(response.responseText);

        this.parentPanel.tree.getStore().load();
        pimcore.helpers.showNotification(t("success"), t("Formbuilder_saved_successfully"), "success");

    },

    saveOnError: function() {
        pimcore.helpers.showNotification(t("error"), t("some_fields_cannot_be_saved"), "error");
    }

});