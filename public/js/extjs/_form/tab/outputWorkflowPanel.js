pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflowPanel');
Formbuilder.extjs.formPanel.outputWorkflowPanel = Class.create({

    formSelectionPanel: null,
    parentPanel: null,
    panel: null,
    editPanel: null,
    tree: null,

    getDataSuccess: true,
    importIsRunning: false,
    formId: null,
    formName: null,
    funnelConfiguration: null,

    activeWorkflowId: null,
    loading: false,

    initialize: function (formData, formSelectionPanel) {
        this.formSelectionPanel = formSelectionPanel;
        this.formId = formData.id;
        this.formName = formData.name;
        this.funnelConfiguration = formData.funnel;
        this.activeWorkflowId = null;
    },

    getLayout: function (parentPanel) {

        var _self = this,
            store;

        this.parentPanel = parentPanel;

        store = Ext.create('Ext.data.TreeStore', {
            proxy: {
                type: 'ajax',
                url: '/admin/formbuilder/output-workflow/get-output-workflow-tree/' + this.formId
            },
            listeners: {
                load: function (tree, records, success, opt) {
                    if (opt.outputWorkflowId !== undefined) {
                        var record = _self.tree.getRootNode().findChild('id', opt.outputWorkflowId, true);
                        _self.tree.getSelectionModel().select(record);
                    }
                }
            }
        });

        this.tree = Ext.create('Ext.tree.Panel', {
            region: 'west',
            listeners: this.getTreeNodeListeners(),
            store: store,
            autoScroll: true,
            animate: true,
            split: true,
            width: 200,
            cls: 'form-builder-output-workflow-elements-tree',
            rootVisible: false,
            root: {
                draggable: false,
                allowChildren: false,
                id: '0',
                expanded: true
            },
            selModel: {
                ignoreRightMouseSelection: true
            },
            tbar: {
                items: [
                    {
                        text: t('form_builder.output_workflow.add_output_workflow'),
                        iconCls: 'pimcore_icon_output_workflow_add',
                        handler: this.addOutputWorkflow.bind(this)
                    }
                ]
            }
        });

        this.editPanel = new Ext.Panel({
            region: 'center',
            bodyStyle: 'padding: 10px;',
            cls: 'form-builder-form-output-workflow-panel',
            autoScroll: true,
            border: false,
        });

        this.panel = new Ext.Panel({
            title: t('form_builder.tab.output_workflow'),
            closable: false,
            iconCls: 'pimcore_icon_output_workflow',
            autoScroll: true,
            border: false,
            layout: 'border',
            items: [this.tree, this.editPanel]
        });

        this.panel.on('beforedestroy', function () {
            if (this.formId) {
                this.editPanel.removeAll();
            }
        }.bind(this));

        return this.panel;
    },

    remove: function () {
        this.panel.destroy();
    },

    clearEditPanel: function () {
        this.releaseTree();
        this.editPanel.removeAll();
        this.tree.getSelectionModel().select(this.tree.getRootNode(), true);
        this.activeWorkflowId = null;
    },

    getTreeNodeListeners: function () {

        return {
            beforeselect: this.onTreeNodeBeforeSelect.bind(this),
            select: this.onTreeNodeSelect.bind(this),
            itemcontextmenu: this.onTreeNodeContextMenu.bind(this),
            render: function () {
                this.getRootNode().expand();
            },
            beforeitemappend: function (thisNode, newChildNode) {
                newChildNode.data.qtip = t('id') + ': ' + newChildNode.data.id;
            }
        };
    },

    releaseTree: function () {

        this.tree.getDockedItems('toolbar[dock="top"]')[0].items.each(function (btn) {
            btn.cls = '';
        });

        this.tree.getRootNode().cascade(function (record) {
            record.set('cls', '');
        });
    },

    lockTree: function (selectedRecord) {

        this.tree.getDockedItems('toolbar[dock="top"]')[0].items.each(function (btn) {
            btn.cls = 'formbuilder-object-editor-disabled';
        });

        this.tree.getRootNode().cascade(function (record) {
            if (selectedRecord.get('id') !== record.get('id')) {
                record.set('cls', 'formbuilder-object-editor-disabled');
            }
        });
    },

    onTreeNodeBeforeSelect: function (view, record) {
        if (record.get('cls') === 'formbuilder-object-editor-disabled') {
            Ext.Msg.alert(t('error'), t('form_builder.tab.output_workflow_locked'));
            return false;
        }
    },

    onTreeNodeSelect: function (tree, selectedRecord) {
        if (!selectedRecord.isLeaf()) {
            return;
        }

        this.createOutputWorkflowPanel(selectedRecord.get('id'));
        this.lockTree(selectedRecord);
    },

    onTreeNodeContextMenu: function (tree, record, item, index, e) {

        var menu = new Ext.menu.Menu();

        e.stopEvent();

        if (record.get('cls') === 'formbuilder-object-editor-disabled') {
            return;
        }

        menu.add(new Ext.menu.Item({
            text: t('delete'),
            iconCls: 'pimcore_icon_delete',
            handler: this.deleteOutputWorkflow.bind(this, tree, record)
        }));

        menu.on('hide', function (menu) {
            menu.destroy()
        }, this, {delay: 200});

        menu.showAt(e.pageX, e.pageY);
    },

    addOutputWorkflow: function (btn) {

        var messageBox;

        if (this.formId === null) {
            Ext.Msg.alert(t('error'), 'You need to save your form first.');
            return;
        }

        if (btn.cls === 'formbuilder-object-editor-disabled') {
            Ext.Msg.alert(t('error'), t('form_builder.tab.output_workflow_locked'));
            return;
        }

        messageBox = new Ext.Window({
            modal: true,
            width: 500,
            closeAction: 'destroy',
            title: t('form_builder.output_workflow.new_output_workflow_name_title'),
            bodyStyle: 'padding: 10px 10px 0px 10px',
            buttonAlign: 'center',
            items: [
                {
                    xtype: 'container',
                    html: t('form_builder.output_workflow.new_output_workflow_name'),
                },
                {
                    xtype: 'textfield',
                    width: '100%',
                    name: 'output_workflow_name',
                    itemId: 'output_workflow_name',
                    listeners: {
                        afterrender: function () {
                            window.setTimeout(function () {
                                this.focus(true);
                            }.bind(this), 100);
                        }
                    }
                },
                {
                    xtype: 'checkbox',
                    boxLabel: t('form_builder.output_workflow.new_output_workflow_funnel_aware'),
                    name: 'output_workflow_funnel_aware',
                    itemId: 'output_workflow_funnel_aware',
                    checked: false,
                    hidden: this.funnelConfiguration.enabled === false
                }
            ],
            buttons: [
                {
                    text: t('OK'),
                    handler: this.createNewOutputWorkflow.bind(this),
                },
                {
                    text: t('cancel'),
                    handler: function () {
                        messageBox.close();
                    }
                }
            ]
        });

        messageBox.show();
    },

    createNewOutputWorkflow: function (button) {

        var owWindow = button.up('window'),
            owName = owWindow.getComponent('output_workflow_name').getValue(),
            owFunnelAware = owWindow.getComponent('output_workflow_funnel_aware').getValue();

        owWindow.close();

        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/add-output-workflow/' + this.formId,
            method: 'POST',
            params: {
                outputWorkflowName: owName,
                outputWorkflowFunnelAware: owFunnelAware,
            },
            success: function (response) {

                var data = Ext.decode(response.responseText);
                this.tree.getStore().load({'outputWorkflowId': data.id});

                if (!data || !data.success) {
                    Ext.Msg.alert(t('form_builder.output_workflow.new_output_workflow_name_title'), data.message);
                } else {
                    this.createOutputWorkflowPanel(intval(data.id));
                }

            }.bind(this)
        });
    },

    createOutputWorkflowPanel: function (id) {

        if (this.loading === true) {
            return;
        }

        // already open!
        if (id === this.activeWorkflowId) {
            return;
        }

        this.loading = true;
        this.tree.disable();
        this.editPanel.removeAll();

        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/get-output-workflow-data/' + id,
            success: this.createOutputWorkflowChannelsPanel.bind(this)
        });
    },

    createOutputWorkflowChannelsPanel: function (response) {

        var responseData = Ext.decode(response.responseText),
            formPanel;

        this.loading = false;
        this.tree.enable();

        if (responseData.success === false) {
            Ext.MessageBox.alert(t('error'), t('form_builder.output_workflow.output_workflow_invalid_configuration') + responseData.message);
            return;
        }

        this.activeWorkflowId = responseData.data.id;

        formPanel = new Formbuilder.extjs.formPanel.outputWorkflow.configPanel(responseData.data, this.formId, this);

        this.editPanel.add(formPanel.getLayout());
    },

    deleteOutputWorkflow: function (tree, record) {

        Ext.Msg.confirm(t('delete'), t('form_builder.output_workflow.output_workflow_confirm_deletion'), function (btn) {

            if (btn !== 'yes') {
                return;
            }

            this.loading = true;
            this.tree.disable();

            Ext.Ajax.request({
                url: '/admin/formbuilder/output-workflow/delete-output-workflow/' + record.id,
                success: function (response) {

                    var res = Ext.decode(response.responseText);

                    this.loading = false;
                    this.tree.enable();

                    if (res.success === false) {
                        Ext.MessageBox.alert(t('error'), res.message);
                        return;
                    }

                    // remove active edit panel => it's the deleted one!
                    if (record.id === this.activeWorkflowId) {
                        this.clearEditPanel();
                    }

                    record.remove();
                    this.releaseTree();

                    Formbuilder.eventObserver
                    .getObserver(this.formId)
                    .fireEvent('output_workflow.required_form_fields_refreshed', {workflowId: record.id});
                }.bind(this),

                failure: function () {
                    this.loading = false;
                    this.tree.enable();
                    pimcore.helpers.showNotification(t('error'), t('error'), 'error');
                }.bind(this)
            });

        }.bind(this));
    }
});