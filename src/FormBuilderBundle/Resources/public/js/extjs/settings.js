pimcore.registerNS('Formbuilder.eventObserver');
pimcore.registerNS('Formbuilder.settings');
Formbuilder.settings = Class.create({

    usedFormNames: [],
    config: {},

    initialize: function (config) {
        this.panels = {};
        this.config = config.settings;
        this.loading = false;
        this.getTabPanel();
    },

    getConfig: function () {
        return this.config;
    },

    getTabPanel: function () {

        var tabPanel;

        if (this.panel) {
            return this.panel;
        }

        Formbuilder.eventObserver = new FormbuilderEventObserver();

        this.panel = new Ext.Panel({
            id: 'form_builder_settings',
            title: t('form_builder_settings'),
            border: false,
            iconCls: 'form_builder_icon_fbuilder',
            layout: 'border',
            closable: true,
            items: [this.getMainTree(), this.getEditPanel()]
        });

        tabPanel = Ext.getCmp('pimcore_panel_tabs');
        tabPanel.add(this.panel);
        tabPanel.setActiveItem('form_builder_settings');

        this.panel.on('destroy', function () {
            Formbuilder.eventObserver = null;
            pimcore.globalmanager.remove('form_builder_settings');
        }.bind(this));

        pimcore.layout.refresh();

        return this.panel;
    },

    getMainTree: function () {

        var _self = this,
            store;

        if (this.tree) {
            return this.tree;
        }

        store = Ext.create('Ext.data.TreeStore', {
            proxy: {
                type: 'ajax',
                url: '/admin/formbuilder/settings/get-tree'
            },
            listeners: {
                load: function (tree, records, success, opt) {

                    Ext.each(records, function (record) {
                        if (!in_array(record.data.text, _self.usedFormNames)) {
                            _self.usedFormNames.push(record.data.text);
                        }
                    }, this);

                    //new form added, mark as selected!
                    if (opt.formId !== undefined) {
                        var record = _self.tree.getRootNode().findChild('id', opt.formId, true);
                        _self.tree.getSelectionModel().select(record);
                    }
                }
            }
        });

        this.tree = new Ext.tree.TreePanel({
            id: 'form_builder_panel_settings_tree',
            region: 'west',
            store: store,
            autoScroll: true,
            animate: true,
            containerScroll: true,
            split: true,
            width: 200,
            cls: 'form-builder-form-selector-tree',
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
                        handler: this.addNewForm.bind(this)
                    }
                ]
            }
        });

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
                    tabchange: function (tabpanel, tab) {
                        var record = this.tree.getRootNode().findChild('id', tab.id, true);
                        this.tree.getSelectionModel().select(record);
                    }.bind(this)
                }
            });
        }

        return this.editPanel;
    },

    getTreeNodeListeners: function () {

        return {
            itemclick: this.onTreeNodeClick.bind(this),
            itemcontextmenu: this.onTreeNodeContextMenu.bind(this),
            render: function () {
                this.getRootNode().expand();
            },
            beforeitemappend: function (thisNode, newChildNode) {
                newChildNode.data.qtip = t('id') + ': ' + newChildNode.data.id;
            }
        };
    },

    onTreeNodeClick: function (tree, record) {
        if (!record.isLeaf()) {
            return;
        }
        this.createFormConfigurationPanel(record.data.id);
    },

    onTreeNodeContextMenu: function (tree, record, item, index, e) {

        var menu;

        e.stopEvent();
        tree.select();

        if (!record.isLeaf()) {
            return;
        }

        menu = new Ext.menu.Menu();
        menu.add(new Ext.menu.Item({
            text: t('delete'),
            iconCls: 'pimcore_icon_delete',
            handler: this.deleteForm.bind(this, tree, record)
        }));

        menu.showAt(e.pageX, e.pageY);
    },

    addNewForm: function () {
        Ext.MessageBox.prompt(
            t('form_builder_add_elem'),
            t('form_builder_enter_name_of_new_elem'),
            this.createNewForm.bind(this),
            null, null, ''
        );
    },

    createNewForm: function (button, value) {

        if (button === 'cancel') {
            return false;
        }

        if (value.length <= 2 || in_array(value.toLowerCase(), this.getConfig().forbidden_form_field_names)) {
            Ext.Msg.alert(t('error'), t('form_builder_problem_creating_new_elem'));
            return false;
        }

        if (in_array(value, this.usedFormNames)) {
            Ext.Msg.alert(t('error'), t('form_builder_some_fields_names_double'), 'error');
            return false;
        }

        this.usedFormNames.push(value);

        Ext.Ajax.request({
            url: '/admin/formbuilder/settings/add-form',
            params: {
                form_name: value
            },
            success: function (response) {

                var data = Ext.decode(response.responseText);
                this.tree.getStore().load({'formId': data.id});

                if (!data || !data.success) {
                    Ext.Msg.alert(t('form_builder_add_elem'), data.message);
                } else {
                    this.createFormConfigurationPanel(intval(data.id));
                }

            }.bind(this)
        });
    },

    createFormConfigurationPanel: function (id) {

        var formPanelKey = 'form_' + id;

        if (this.loading === true) {
            return;
        }

        //its already loaded
        if (this.panels[formPanelKey]) {
            this.panels[formPanelKey].activate();
        } else {
            this.loading = true;
            this.tree.disable();
            Ext.Ajax.request({
                url: '/admin/formbuilder/settings/get-form',
                params: {
                    id: id
                },
                success: this.createFormPanel.bind(this)
            });
        }
    },

    createFormPanel: function (response) {

        var responseData = Ext.decode(response.responseText),
            formPanel, data, formPanelKey;

        this.loading = false;
        this.tree.enable();

        if (responseData.success === false) {
            Ext.MessageBox.alert(t('error'), t('form_builder_invalid_form_type_configuration') + responseData.message);
            return;
        }

        data = responseData.data;
        formPanelKey = 'form_' + data.id;
        formPanel = new Formbuilder.extjs.rootForm(data, this);

        this.panels[formPanelKey] = formPanel;

        pimcore.layout.refresh();
    },

    deleteForm: function (tree, record) {

        Ext.Msg.confirm(t('delete'), t('form_builder_delete_form_warn'), function (btn) {

            if (btn !== 'yes') {
                return;
            }

            var i = this.usedFormNames.indexOf(record.data.text);
            if (i !== -1) {
                this.usedFormNames.splice(i, 1);
            }

            Ext.Ajax.request({
                url: '/admin/formbuilder/settings/delete-form',
                params: {
                    id: record.id
                }
            });

            if (this.panels['form_' + record.id]) {
                this.panels['form_' + record.id].remove();
            }

            this.getEditPanel().remove(record.id);
            record.remove();

        }.bind(this));
    },

    activate: function () {
        Ext.getCmp('pimcore_panel_tabs').setActiveItem('form_builder_settings');
    }
});