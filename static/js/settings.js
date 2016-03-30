pimcore.registerNS("Formbuilder.settings");
Formbuilder.settings = Class.create({

    forbiddennames: ["abstract","class","data","folder","list","permissions","resource","concrete","interface",
        "service", "fieldcollection", "localizedfield", "objectbrick"],

    usedFormNames: [],

    initialize: function () {

        this.panels = {};

        this.getTabPanel();

    },

    getTabPanel: function () {

        if (!this.panel) {


            //DEBUG START
            // refreshes the layout
            pimcore.registerNS("pimcore.layout.refresh");
            pimcore.layout.refresh = function () {
                try {
                    pimcore.viewport.doLayout();
                }
                catch (e) {
                }
            };

            pimcore.viewport = new Ext.Viewport({
                id:"pimcore_viewport",
                layout:'fit',
                items:[
                    {
                        xtype:"panel",
                        id:"pimcore_body",
                        cls:"pimcore_body",
                        layout:"border",
                        border:false,
                        items:[

                            new Ext.TabPanel({
                                region:'center',
                                deferredRender:false,
                                id:"pimcore_panel_tabs",
                                enableTabScroll:true,
                                hideMode:"offsets",
                                cls:"tab_panel"

                            })
                        ]
                    }
                ]
            });

            //DEBUG END


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
            //tabPanel.setActiveItem(this.panel);

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("Formbuilder");
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
                    url: '/plugin/Formbuilder/settings/get-tree',
                },

                listeners : {
                    load : function(tree, records, successful) {

                        Ext.each(records, function(record, index){
                            if( !in_array(record.data.text, _self.usedFormNames)) {
                                _self.usedFormNames.push( record.data.text);
                            }
                        }, this);

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
                border: true,
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
                layout: "fit"
            });
        }

        return this.editPanel;
    },

    getTreeNodeListeners: function () {

        var treeNodeListeners = {
            "itemclick" : this.onTreeNodeClick.bind(this),
            "itemcontextmenu": this.onTreeNodeContextmenu.bind(this),
            "render": function () {
                this.getRootNode().expand();
            },
            "beforeitemappend": function (thisNode, newChildNode, index, eOpts) {
                newChildNode.data.qtip = t('id') +  ": " + newChildNode.data.id;
            }
        };

        return treeNodeListeners;
    },

    onTreeNodeClick: function (tree, record) {
        this.openFormConfig(record.data.id);
    },

    openFormConfig : function(id) {

        Ext.Ajax.request({
            url: "/plugin/Formbuilder/Settings/get",
            params: {
                id: id
            },
            success: this.addMainPanel.bind(this)
        });

    },

    addMainPanel: function (response) {

        var data = Ext.decode(response.responseText);

        var formPanelKey = "form_" + data.id;

        if(this.panels[formPanelKey]) {

            this.panels[formPanelKey].activate();

        } else {

            var formPanel = new Formbuilder.comp.elem(data, this);
            this.panels[formPanelKey] = formPanel;

        }

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
        if (button == "ok" && value.length > 2 && regresult == value
                                                && !in_array(value.toLowerCase(), this.forbiddennames)) {

            if( in_array(value, this.usedFormNames) ) {

                Ext.Msg.alert(t("error"), t("some_fields_names_are_in_double"), "error");

            } else {

                this.usedFormNames.push(value);

                 Ext.Ajax.request({
                     url: "/plugin/Formbuilder/Settings/add",
                     params: {
                        name: value
                     },
                     success: function (response) {

                         var data = Ext.decode(response.responseText);

                         this.tree.getStore().load();

                         if(!data || !data.success) {

                            Ext.Msg.alert(t('add_elem'), t('problem_creating_new_elem'));

                         } else {

                             this.openFormConfig(intval(data.id));

                         }

                     }.bind(this)
                 });

            }

        }

        else if (button == "cancel") {

            return;

        } else {

            Ext.Msg.alert(t('add_elem'), t('problem_creating_new_elem'));

        }

    },

    deleteMain: function (tree, record) {

        var i = this.usedFormNames.indexOf( record.data.text );
        if(i != -1) {
            this.usedFormNames.splice(i, 1);
        }

        Ext.Ajax.request({
            url: "/plugin/Formbuilder/Settings/delete",
            params: {
                id: record.id
            }
        });

        this.getEditPanel().removeAll();
        record.remove();

    },

    activate: function () {

        Ext.getCmp("pimcore_panel_tabs").activate("Formbuilder_settings");

    }

});