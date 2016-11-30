pimcore.registerNS("Formbuilder.settings");
Formbuilder.settings = Class.create({

    forbiddennames: ["abstract","class","data","folder","list","permissions","resource","concrete","interface",
        "service", "fieldcollection", "localizedfield", "objectbrick"],

    usedFormNames: [],

    initialize: function () {

        this.panels = {};

        this.loading = false;

        this.getTabPanel();

    },

    getTabPanel: function () {

        if (!this.panel) {

            this.panel = new Ext.Panel({

                id: "Formbuilder_settings",
                title: t("Formbuilder_settings"),
                border: false,
                iconCls:"Formbuilder_icon_fbuilder",
                layout: "border",
                closable:true,
                items: [this.getMainTree(), this.getEditPanel()]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("Formbuilder_settings");

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("Formbuilder_settings");
            }.bind(this));

            pimcore.layout.refresh();

        }

        return this.panel;
    },

    getMainTree: function () {

        var _self = this;
        if (!this.tree) {

            var store = Ext.create('Ext.data.TreeStore', {
                proxy: {
                    type: 'ajax',
                    url: '/plugin/Formbuilder/admin_Settings/get-tree'
                },

                listeners : {
                    load : function(tree, records, success, opt) {

                        Ext.each(records, function(record){
                            if( !in_array(record.data.text, _self.usedFormNames)) {
                                _self.usedFormNames.push( record.data.text );
                            }
                        }, this);

                        //new form added, mark es selected!
                        if( opt.formId !== undefined ) {

                            var record = _self.tree.getRootNode().findChild('id',opt.formId,true);
                            _self.tree.getSelectionModel().select(record);

                        }

                    }

                }

            });

            this.tree = new Ext.tree.TreePanel({
                id: "Formbuilder_panel_settings_tree",
                region: "west",
                store: store,
                autoScroll:true,
                animate:true,
                containerScroll: true,
                split: true,
                width: 200,

                root: {
                    draggable: false,
                    allowChildren: false,
                    id: '0',
                    expanded: true
                },

                rootVisible: false,
                listeners: this.getTreeNodeListeners(),
                tbar: {
                    items: [
                        {
                            text: t("add_form"),
                            iconCls: "Formbuilder_icon_root_add",
                            handler: this.addMain.bind(this)
                        }
                    ]
                }
            });

        }

        this.tree.getRootNode().expand();

        return this.tree;
    },

    getEditPanel: function () {

        if (!this.editPanel) {
            this.editPanel = new Ext.TabPanel({
                activeTab: 0,
                items: [],
                region: "center",
                layout: "fit",
                listeners: {
                    tabchange: function(tabpanel, tab) {

                        var record = this.tree.getRootNode().findChild('id',tab.id,true);
                        this.tree.getSelectionModel().select(record);

                    }.bind(this)
                }
            });
        }

        return this.editPanel;
    },

    getTreeNodeListeners: function () {

        return {

            itemclick : this.onTreeNodeClick.bind(this),
            itemcontextmenu: this.onTreeNodeContextmenu.bind(this),
            render: function () {
                this.getRootNode().expand();
            },
            beforeitemappend: function (thisNode, newChildNode, index, eOpts) {
                newChildNode.data.qtip = t('id') +  ": " + newChildNode.data.id;
            }

        };

    },

    onTreeNodeClick: function (tree, record) {
        this.openFormConfig(record.data.id);
    },

    openFormConfig: function(id) {

        var formPanelKey = "form_" + id;

        if( this.loading === true ) {
            return false;
        }

        //its already loaded
        if( this.panels[formPanelKey] ) {

            this.panels[formPanelKey].activate();

        } else {

            this.loading = true;
            this.tree.disable();

            Ext.Ajax.request({
                url: "/plugin/Formbuilder/admin_Settings/get",
                params: {
                    id: id
                },
                success: this.addMainPanel.bind(this)
            });
        }


    },

    addMainPanel: function (response) {

        var formPanel,
            data = Ext.decode(response.responseText),
            formPanelKey = "form_" + data.id;

        this.loading = false;
        this.tree.enable();

        formPanel = new Formbuilder.comp.elem(data, this);
        this.panels[formPanelKey] = formPanel;

        pimcore.layout.refresh();

    },

    onTreeNodeContextmenu: function (tree, record, item, index, e, eOpts) {

        e.stopEvent();
        tree.select();

        var menu = new Ext.menu.Menu();

        menu.add(new Ext.menu.Item({
            text: t('delete'),
            iconCls: "pimcore_icon_delete",
            handler: this.deleteMain.bind(this, tree, record)

        }));

        menu.showAt(e.pageX, e.pageY);
    },

    addMain: function () {

        Ext.MessageBox.prompt(t('add_elem'), t('enter_the_name_of_the_new_elem'), this.addMainComplete.bind(this), null, null, "");

    },

    addMainComplete: function (button, value, object) {

        var regresult = value.match(/[a-zA-Z]+/);

        if (button === "ok" && value.length > 2 && regresult == value && !in_array(value.toLowerCase(), this.forbiddennames)) {

            if( in_array(value, this.usedFormNames) ) {

                Ext.Msg.alert(t("error"), t("some_fields_names_are_in_double"), "error");

            } else {

                this.usedFormNames.push(value);

                Ext.Ajax.request({
                    url: "/plugin/Formbuilder/admin_Settings/add",
                    params: {
                        name: value
                    },
                    success: function(response) {

                        var data = Ext.decode(response.responseText);

                        this.tree.getStore().load({ 'formId' : data.id });

                        if(!data || !data.success) {

                            Ext.Msg.alert(t('add_elem'), t('problem_creating_new_elem'));

                        } else {

                            this.openFormConfig(intval(data.id));

                        }

                    }.bind(this)
                });

            }

        } else if (button === "cancel") {

            return false;

        } else {

            Ext.Msg.alert(t('add_elem'), t('problem_creating_new_elem'));

        }

    },

    deleteMain: function (tree, record) {

        Ext.Msg.confirm(t("delete"), t("Do you really want to delete this form? this can't be undone!"), function(btn){

            if (btn === "yes") {

                var i = this.usedFormNames.indexOf( record.data.text );
                if(i != -1) {
                    this.usedFormNames.splice(i, 1);
                }

                Ext.Ajax.request({
                    url: "/plugin/Formbuilder/admin_Settings/delete",
                    params: {
                        id: record.id
                    }
                });

                this.getEditPanel().remove( record.id );
                record.remove();

            }

        }.bind(this));

    },

    activate: function () {

        Ext.getCmp("pimcore_panel_tabs").setActiveItem("Formbuilder_settings");

    }

});