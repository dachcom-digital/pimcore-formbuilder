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

    loading: false,

    activeWorkflowId: null,

    initialize: function (formData, formSelectionPanel) {
        this.activeWorkflowId = null;
        this.formSelectionPanel = formSelectionPanel;
        this.formId = formData.id;
        this.formName = formData.name;
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
                    if (opt.formId !== undefined) {
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
            containerScroll: true,
            split: true,
            width: 200,
            cls: 'form-builder-output-workflow-elements-tree',
            root: {
                draggable: false,
                allowChildren: false,
                id: '0',
                expanded: true
            },
            rootVisible: false,
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
            autoScroll: true
        });

        this.panel = new Ext.Panel({
            title: 'Output Workflow',
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
        this.createOutputWorkflowPanel(record.data.id);
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
            handler: this.deleteOutputWorkflow.bind(this, tree, record)
        }));

        menu.showAt(e.pageX, e.pageY);
    },

    addOutputWorkflow: function () {

        if (this.formId === null) {
            Ext.Msg.alert(t('error'), 'You need to save your form first.');
            return;
        }

        Ext.MessageBox.prompt(
            t('form_builder.output_workflow.new_output_workflow_name_title'),
            t('form_builder.output_workflow.new_output_workflow_name'),
            this.createNewOutputWorkflow.bind(this),
            null, null, ''
        );
    },

    createNewOutputWorkflow: function (button, value) {

        if (button !== 'ok') {
            return false;
        }

        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/add-output-workflow/' + this.formId,
            method: 'POST',
            params: {
                outputWorkflowName: value
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

        /** @todo: ask for unsaved changes! **/
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
        formPanel = new Formbuilder.extjs.formPanel.outputWorkflow.configPanel(responseData.data, this);

        this.editPanel.add(formPanel.getLayout());
    },

    deleteOutputWorkflow: function (tree, record) {

        Ext.Msg.confirm(t('delete'), t('form_builder.output_workflow.output_workflow_confirm_deletion'), function (btn) {

            if (btn !== 'yes') {
                return;
            }

            Ext.Ajax.request({
                url: '/admin/formbuilder/output-workflow/delete-output-workflow/' + record.id
            });

            // remove active edit panel => it's the deleted one!
            if (record.id === this.activeWorkflowId) {
                this.editPanel.removeAll();
            }

            record.remove();

        }.bind(this));
    }
});