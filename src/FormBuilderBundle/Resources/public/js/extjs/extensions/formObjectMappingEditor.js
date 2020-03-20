pimcore.registerNS('Formbuilder.extjs.extensions.formObjectMappingEditor');
Formbuilder.extjs.extensions.formObjectMappingEditor = Class.create({

    formId: null,
    additionalParameter: {},
    baseConfiguration: {},
    isLocal: null,
    callbacks: null,

    configuration: null,
    editorData: null,
    forceClose: false,

    detailWindow: null,
    editPanel: null,

    formTreePanel: null,
    classTreePanel: null,
    classDefinitionTreePanel: null,

    initialize: function (formId, additionalParameter, baseConfiguration, isLocal, callbacks) {

        this.formId = formId;
        this.additionalParameter = additionalParameter ? additionalParameter : {};
        this.baseConfiguration = baseConfiguration ? baseConfiguration : {};
        this.isLocal = isLocal === true;
        this.callbacks = callbacks;

        this.forceClose = false;

        this.loadObjectEditor();
    },

    checkClose: function (win) {

        if (this.forceClose === true) {
            win.closeMe = true;
            return true;
        }

        if (win.closeMe) {
            win.closeMe = false;
            return true;
        }

        Ext.Msg.show({
            title: t('form_builder.output_workflow.output_workflow_channel.object.close_confirmation_title'),
            msg: t('form_builder.output_workflow.output_workflow_channel.object.close_confirmation_message'),
            buttons: Ext.Msg.YESNO,
            callback: function (btn) {
                if (btn === 'yes') {
                    win.closeMe = true;
                    win.close();
                }
            }
        });

        return false;
    },

    onClose: function (editorId) {

    },

    loadObjectEditor: function () {

        if (this.detailWindow !== null) {
            return this.detailWindow;
        }

        this.detailWindow = new Ext.Window({
            width: 1200,
            height: 768,
            iconCls: 'form_builder_output_workflow_channel_object_mapper',
            layout: 'fit',
            closeAction: 'destroy',
            plain: true,
            autoScroll: true,
            preventRefocus: true,
            cls: 'formbuilder-object-mapping-editor',
            modal: true,
            listeners: {
                beforeClose: this.checkClose.bind(this)
            },
            buttons: [
                {
                    text: t('save'),
                    iconCls: 'pimcore_icon_save',
                    handler: this.saveEditorData.bind(this)
                },
                {
                    text: t('save_close'),
                    iconCls: 'pimcore_icon_save',
                    handler: this.saveEditorDataAndClose.bind(this)
                },
                {
                    text: t('close'),
                    iconCls: 'pimcore_icon_empty',
                    handler: function () {
                        this.detailWindow.close();
                    }.bind(this)
                }
            ]
        });

        this.detailWindow.show();

        this.loadEditorData();

    },

    loadEditorData: function () {

        var loadSuccess = function (data) {
            this.editorData = this.isLocal ? this.callbacks.loadData() : data.data;
            this.configuration = data.configuration;
            this.detailWindow.setLoading(false);

            this.createPanel();
        }.bind(this);

        this.detailWindow.setLoading(true);

        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/object/get-form-data',
            params: {
                id: this.formId,
                baseConfiguration: Ext.encode(this.baseConfiguration),
                additionalParameter: this.additionalParameter,
                externalData: this.isLocal === true
            },
            success: function (resp) {
                var data = Ext.decode(resp.responseText);

                if (data.success === true) {
                    loadSuccess(data);
                } else {
                    this.detailWindow.setLoading(false);
                    Ext.Msg.alert(t('error'), data.message);
                }
            }.bind(this)
        });
    },

    createPanel: function () {

        var intervalCounter = 0,
            intervalInstance;

        this.editPanel = new Ext.Panel({
            layout: 'border',
            autoScroll: true,
            border: false
        });

        // this is so evil. but pimcore's tree builder is a real mess,
        // so we need to check via interval the store status
        intervalInstance = setInterval(function () {
            intervalCounter = intervalCounter + 1;

            if (intervalCounter > 20) {
                clearInterval(intervalInstance);
            }

            if (!this.classTreePanel) {
                return;
            }

            if (this.classTreePanel.getRootNode().childNodes.length > 0) {
                clearInterval(intervalInstance);
                this.editPanel.add(this.getFormTreePanel());
            }

        }.bind(this), 100);

        this.editPanel.add([this.getClassDefinitionTreePanel()]);

        this.detailWindow.add(this.editPanel);

        this.detailWindow.addDocked({
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
                {xtype: 'label', html: '<strong>Resolve Strategy</strong>: ' + this.baseConfiguration.resolveStrategy},
                {xtype: 'label', html: '<strong>Data Class</strong>: ' + this.configuration.className},
            ]
        });
    },

    getFormTreePanel: function () {

        var treeItems = [];

        Ext.Array.each(this.configuration.formFieldDefinitions, function (field) {

            var fieldData = field['data'],
                fieldTypeConfig = field['type'];

            var item = {
                text: fieldData['display_name'] + ' (' + fieldData['name'] + ')',
                type: 'layout',
                iconCls: fieldTypeConfig['icon_class'],
                leaf: false,
                allowDrag: false,
                draggable: false,
                expandable: false,
                expanded: true,
                omFieldTypeIdentifier: 'form_field',
                omFieldAttributes: {
                    data: fieldData,
                    typeConfig: fieldTypeConfig
                },
                isAllowedInClassType: function (sourceNode) {
                    var pimcoreDataTypeConfig, pimcoreDataType;
                    if (!sourceNode.hasOwnProperty('data')) {
                        return false;
                    }

                    pimcoreDataTypeConfig = sourceNode.data;
                    pimcoreDataType = pimcoreDataTypeConfig.dataType;

                    //@todo: make configurable!
                    if (pimcoreDataType === 'input') {
                        return true;
                    }

                    return false;
                }
            };

            item = this.resolveItemChildren(item);

            treeItems.push(item);

        }.bind(this));

        this.formTreePanel = new Ext.tree.TreePanel({
            region: 'center',
            title: 'Form Data',
            layout: 'fit',
            split: true,
            rootVisible: true,
            autoScroll: true,
            listeners: {
                //itemcontextmenu: this.onTreeNodeContextmenu.bind(this)
            },
            root: {
                id: '0',
                fbType: 'root',
                fbTypeContainer: 'root',
                text: t('form_builder_base'),
                iconCls: 'form_builder_icon_root',
                isTarget: true,
                leaf: false,
                root: true,
                expanded: true,
                children: treeItems
            },
            columns: [
                {
                    xtype: 'treecolumn',
                    text: 'Task',
                    dataIndex: 'text',
                    flex: 2,
                    sortable: false,
                    renderer: function (value, metaData, record) {

                        if (record.data && record.data.configAttributes && record.data.configAttributes.class == "Ignore") {
                            metaData.tdCls += ' pimcore_import_operator_ignore';
                        }

                        return value;
                    }
                },
                {
                    text: t('col_label'),
                    dataIndex: 'iconCls',
                    flex: 2,
                    sortable: true
                }
            ],
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    ddGroup: 'columnconfigelement'
                },
                listeners: {
                    beforedrop: function (node, data, overModel, dropPosition, dropHandlers) {

                        var target = overModel.getOwnerTree().getView(),
                            source = data.view,
                            record, attr, copy;

                        record = data.records[0];
                        attr = record.data;

                        if (target !== source) {

                            attr.omFieldTypeIdentifier = 'data_class_field';
                            copy = record.createNode(attr);

                            data.records = [copy];

                        } else {

                            // node has been moved inside the left selection panel

                            //@todo: check if field has configuration
                            //if (record.data.specialConfiguration) {
                                // there is nothing to do (only if operator, not available currently
                                //return;
                            //}

                            copy = record.createNode(attr);
                            data.records = [copy];

                            record.parentNode.removeChild(record);
                        }

                    }.bind(this),
                    drop: function (node, data, overModel) {

                        var record = data.records[0];
                        record.set("csvLabel", null, {
                            dirty: false
                        });

                    }.bind(this),
                    nodedragover: function (targetNode, dropPosition, dragData) {

                        var sourceNode, realOverModel;

                        if (dropPosition !== 'append') {
                            return false;
                        }

                        sourceNode = dragData.records[0];
                        realOverModel = targetNode;

                        if (typeof realOverModel.data.isAllowedInClassType !== 'function') {
                            return false;
                        }

                        return realOverModel.data.isAllowedInClassType(sourceNode);

                    }.bind(this),
                    options: {
                        target: this.formTreePanel
                    }
                }
            }
        });

        this.formTreePanel.getStore().getModel().setProxy({
            type: 'memory'
        });

        return this.formTreePanel;
    },

    getClassDefinitionTreePanel: function () {

        if (!this.classTreePanel) {
            this.classTreePanel = this.getClassTree('/admin/class/get-class-definition-for-column-config', this.configuration.classId, 0);
        }

        this.classDefinitionTreePanel = new Ext.Panel({
            layout: 'fit',
            region: 'east',
            width: 600,
            items: [
                this.classTreePanel,
            ]
        });

        return this.classDefinitionTreePanel;
    },

    getClassTree: function (url, classId) {

        var classTreeHelper = new pimcore.object.helpers.classTree(true);

        return classTreeHelper.getClassTree(url, classId);
    },

    saveEditorDataAndClose: function () {
        this.saveEditorData(null, null, function () {
            this.forceClose = true;
            this.detailWindow.close();
        }.bind(this));
    },

    saveEditorData: function (el, ev, callback) {

        var data;

        if (!this.formTreePanel) {
            return;
        }

        data = this.getFormFieldsRecursive(this.formTreePanel.getRootNode());

        if (this.isLocal === true) {
            this.callbacks.saveData(data);
            if (typeof callback === 'function') {
                callback();
            }
            return;
        }

        this.editPanel.setLoading(true);

        //@todo: implement non-local persistence
        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/object/save-object-mapping-data',
            params: {
                id: this.formId,
                data: Ext.encode(data)
            },
            success: function (resp) {

                var data = Ext.decode(resp.responseText);
                this.editPanel.setLoading(false);

                if (data.success === false) {
                    Ext.Msg.alert(t('error'), data.message);
                    return;
                }

                if (typeof callback === 'function') {
                    callback();
                }
            }.bind(this)
        });
    },

    getFormFieldsRecursive: function (node) {
        var elements = [];
        node.eachChild(function (child) {

            var obj = {},
                config = {},
                childData = child.data,
                omFieldTypeIdentifier = childData.omFieldTypeIdentifier,
                omFieldAttributes = childData.hasOwnProperty('omFieldAttributes') ? childData.omFieldAttributes : {};

            obj.childs = this.getFormFieldsRecursive(child);
            obj.type = omFieldTypeIdentifier;

            if (omFieldTypeIdentifier === 'form_field') {
                obj.fieldType = omFieldAttributes.data.type === 'container' ? ('container_' + omFieldAttributes.typeConfig.id) : omFieldAttributes.typeConfig.type;
                config = {name: omFieldAttributes.data.name}
            } else if (omFieldTypeIdentifier === 'data_class_field') {
                obj.fieldType = childData.dataType;
                config = {name: childData.key}
            }

            obj.config = config;

            elements.push(obj);

        }.bind(this));

        return elements;
    },

    resolveItemChildren: function (item) {

        var correspondingConfigNode;

        if (this.editorData === null || !Ext.isArray(this.editorData)) {
            return item;
        }

        correspondingConfigNode = this.findStoredConfigNodeData(item, this.editorData);
        if (correspondingConfigNode === null) {
            return item;
        }

        if (!correspondingConfigNode.hasOwnProperty('childs') || !Ext.isArray(correspondingConfigNode.childs)) {
            return item;
        }

        this.addConfigurationNodesToItem(item, correspondingConfigNode.childs);

        return item;
    },

    findStoredConfigNodeData: function (item, storedData) {

        var d = null;

        Ext.Array.each(storedData, function (fieldData) {

            var itemOmFieldAttributes = item.omFieldAttributes,
                itemOmFieldTypeIdentifier = item.omFieldTypeIdentifier,
                itemName = itemOmFieldAttributes.data.name,

                fieldType = fieldData.type,
                fieldTypeName = fieldData.config.name;

            if (itemOmFieldTypeIdentifier === fieldType && itemName === fieldTypeName) {
                d = fieldData;
                return false;
            }

            if (fieldData.hasOwnProperty('childs') && Ext.isArray(fieldData.childs)) {
                return this.findStoredConfigNodeData(fieldData.childs);
            }
        }.bind(this));

        return d;
    },

    addConfigurationNodesToItem: function (item, correspondingConfigNodes) {

        Ext.Array.each(correspondingConfigNodes, function (subFieldData) {

            var subItem = null,
                type = subFieldData.type,
                fieldType = subFieldData.fieldType,
                fieldTypeConfig = subFieldData.config;

            if (type === 'data_class_field') {
                var record = this.classTreePanel.getRootNode().findChild('key', fieldTypeConfig.name, true);
                if (record) {
                    record.data.omFieldTypeIdentifier = 'data_class_field';
                    subItem = record.createNode(record.data);
                } else {
                    Ext.Msg.alert(t('error'), 'Cannot create data class element "' + type + '" (' + fieldType + ').');
                }
            }

            if (subItem !== null) {
                if (!item.hasOwnProperty('children') || !Ext.isArray(item.children)) {
                    item.children = [];
                }

                item.children.push(subItem);
            }

            if (subItem && subFieldData.hasOwnProperty('childs') && Ext.isArray(subFieldData.childs)) {
                this.addConfigurationNodesToItem(subItem, subFieldData.childs);
            }
        }.bind(this));
    }
});