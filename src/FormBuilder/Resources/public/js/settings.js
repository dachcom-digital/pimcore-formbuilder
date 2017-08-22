pimcore.registerNS('Formbuilder.settings');
Formbuilder.settings = Class.create({

    forbiddennames: [
        'abstract','class','data','folder','list','permissions','resource','concrete','interface',
        'service', 'fieldcollection', 'localizedfield', 'objectbrick'
    ],

    usedFormNames: [],

    config: {},

    initialize: function ( config ) {

        this.panels = {};

        this.config = config;

        this.loading = false;

        this.getTabPanel();

    },

    getConfig: function() {
        return this.config.settings;
    },

    getTabPanel: function () {

        if (!this.panel) {

            this.panel = new Ext.Panel({
                id: 'form_builder_settings',
                title: t('form_builder_settings'),
                border: false,
                iconCls: 'form_builder_icon_fbuilder',
                layout: 'border',
                closable: true,
                items: [ this.getMainTree(), this.getEditPanel() ]
            });

            var tabPanel = Ext.getCmp('pimcore_panel_tabs');
            tabPanel.add(this.panel);
            tabPanel.setActiveItem('form_builder_settings');

            this.panel.on('destroy', function () {
                pimcore.globalmanager.remove('form_builder_settings');
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
                    url: '/admin/formbuilder/settings/get-tree'
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
                id: 'form_builder_panel_settings_tree',
                region: 'west',
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
                            text: t('form_builder_add_form'),
                            iconCls: 'form_builder_icon_root_add',
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
                region: 'center',
                layout: 'fit',
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
            itemcontextmenu: this.onTreeNodeContextMenu.bind(this),
            render: function () {
                this.getRootNode().expand();
            },
            beforeitemappend: function (thisNode, newChildNode, index, eOpts) {
                newChildNode.data.qtip = t('id') +  ': ' + newChildNode.data.id;
            }

        };

    },

    onTreeNodeClick: function (tree, record) {
        this.openFormConfig(record.data.id);
    },

    openFormConfig: function(id) {

        var formPanelKey = 'form_' + id;

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
                url: '/admin/formbuilder/settings/get-form',
                params: {
                    id: id
                },
                success: this.addMainPanel.bind(this)
            });
        }


    },

    addMainPanel: function (response) {

        var responseData = Ext.decode(response.responseText);

        this.loading = false;
        this.tree.enable();

        if(responseData.success === false) {
            Ext.MessageBox.alert(t('error'), t('form_builder_invalid_form_type_configuration') + responseData.message);
            return;
        }

        var formPanel,
            data = responseData.data,
            formPanelKey = 'form_' + data.id;

        formPanel = new Formbuilder.comp.form(data, this);
        this.panels[formPanelKey] = formPanel;

        pimcore.layout.refresh();

    },

    onTreeNodeContextMenu: function (tree, record, item, index, e, eOpts) {

        e.stopEvent();
        tree.select();

        var menu = new Ext.menu.Menu();

        menu.add(new Ext.menu.Item({
            text: t('delete'),
            iconCls: 'pimcore_icon_delete',
            handler: this.deleteMain.bind(this, tree, record)

        }));

        menu.showAt(e.pageX, e.pageY);
    },

    addMain: function () {
        Ext.MessageBox.prompt(t('form_builder_add_elem'), t('form_builder_enter_name_of_new_elem'), this.addMainComplete.bind(this), null, null, '');
    },

    addMainComplete: function (button, value, object) {

        if (button === 'ok' && value.length > 2 && !in_array(value.toLowerCase(), this.forbiddennames)) {

            if( in_array(value, this.usedFormNames) ) {
                Ext.Msg.alert(t('error'), t('form_builder_some_fields_names_double'), 'error');
            } else {

                this.usedFormNames.push(value);

                Ext.Ajax.request({
                    url: '/admin/formbuilder/settings/add-form',
                    params: {
                        form_name: value
                    },
                    success: function(response) {

                        var data = Ext.decode(response.responseText);
                        this.tree.getStore().load({ 'formId' : data.id });

                        if(!data || !data.success) {
                            Ext.Msg.alert(t('form_builder_add_elem'), data.message);
                        } else {
                            this.openFormConfig(intval(data.id));
                        }

                    }.bind(this)
                });

            }

        } else if (button === 'cancel') {
            return false;
        } else {
            Ext.Msg.alert(t('error'), t('form_builder_problem_creating_new_elem'));
        }

    },

    deleteMain: function (tree, record) {

        Ext.Msg.confirm(t('delete'), t('form_builder_delete_form_warn'), function(btn){

            if (btn === 'yes') {

                var i = this.usedFormNames.indexOf( record.data.text );
                if(i != -1) {
                    this.usedFormNames.splice(i, 1);
                }

                Ext.Ajax.request({
                    url: '/admin/formbuilder/settings/delete-form',
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
        Ext.getCmp('pimcore_panel_tabs').setActiveItem('form_builder_settings');
    }

});