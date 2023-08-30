pimcore.registerNS('Formbuilder.extjs.extensions.formObjectMappingEditorConfigurator.formObjectTreeMapper');
Formbuilder.extjs.extensions.formObjectMappingEditorConfigurator.formObjectTreeMapper = Class.create({

    FIELD_TYPE_FORM_BUILDER: 'form_field',
    FIELD_TYPE_DATA_CLASS_FIELD: 'data_class_field',

    formId: null,
    formRootName: null,
    formRootIconCls: null,
    editorData: null,
    formFieldDefinitions: null,
    pimcoreClassType: null,
    pimcoreClassId: null,

    formDataHasInvalidFields: false,
    onlyContainerElementsAllowed: false,

    editPanel: null,
    formTreePanel: null,
    classTreePanel: null,
    classDefinitionTreePanel: null,

    initialize: function (formId, editorData, formFieldDefinitions, pimcoreClassType, pimcoreClassId, formRootName, formRootIconCls) {

        this.formId = formId;
        this.formRootName = formRootName ? formRootName : t('form_builder_base');
        this.formRootIconCls = formRootIconCls ? formRootIconCls : 'form_builder_icon_root';
        this.editorData = editorData;
        this.formFieldDefinitions = formFieldDefinitions;
        this.pimcoreClassType = pimcoreClassType;
        this.pimcoreClassId = pimcoreClassId;

        this.formDataHasInvalidFields = false;
        this.onlyContainerElementsAllowed = false;

    },

    setOnlyContainerElementsAllowed: function () {
        this.onlyContainerElementsAllowed = true;
    },

    getLayout: function () {

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

            if (intervalCounter > 100) {
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
        this.editPanel.on('destroy', function () {
            clearInterval(intervalInstance);
        });

        return this.editPanel;
    },

    buildFormFieldConfigFromNode: function (node) {

        var config, data,
            omFieldAttributes,
            outputWorkflowConfig;

        if (node === null) {
            return null;
        }

        if (!node.hasOwnProperty('data')) {
            return null;
        }

        data = node.data;
        if (!data.hasOwnProperty('omFieldTypeIdentifier')) {
            return null;
        }

        omFieldAttributes = data.hasOwnProperty('omFieldAttributes') ? data.omFieldAttributes : {};

        outputWorkflowConfig = null;
        if (omFieldAttributes.hasOwnProperty('typeConfig')) {
            if (omFieldAttributes.typeConfig.hasOwnProperty('output_workflow')) {
                if (omFieldAttributes.typeConfig.output_workflow.hasOwnProperty('object')) {
                    outputWorkflowConfig = omFieldAttributes.typeConfig.output_workflow.object;
                    if (outputWorkflowConfig.hasOwnProperty('allowed_class_types')) {
                        outputWorkflowConfig['hasAvailableClassTypes'] = outputWorkflowConfig.allowed_class_types.length > 0;
                        outputWorkflowConfig['availableClassTypes'] = outputWorkflowConfig.allowed_class_types;
                    }
                }
            }
        }

        config = {
            omFieldTypeIdentifier: data.omFieldTypeIdentifier,
            omFieldIsDisabled: data.hasOwnProperty('omFieldDisabled') && data.omFieldDisabled === true,
            omFieldTypeNeedsConfiguration: data.hasOwnProperty('omFieldTypeNeedsConfiguration') ? data.omFieldTypeNeedsConfiguration : false,
            omFieldAttributesData: omFieldAttributes.hasOwnProperty('data') ? omFieldAttributes.data : null,
            omFieldAttributesTypeConfig: omFieldAttributes.hasOwnProperty('typeConfig') ? omFieldAttributes.typeConfig : null,
            outputWorkflowConfig: outputWorkflowConfig,
        };

        if (config.omFieldTypeNeedsConfiguration === true) {
            config.omFieldConfigurationWorker = data.omFieldConfigurationWorker;
        }

        return config;
    },

    getFormTreePanel: function () {

        var treeItems,
            _ = this;

        var generateFields = function (fields, treeItems) {

            Ext.Array.each(fields, function (field) {

                var disabledClass,
                    forcedDisabled = false,
                    fieldData = field.data,
                    fieldTypeConfig = field.type,
                    outputWorkflowConfig = fieldTypeConfig.output_workflow.object;

                if (_.onlyContainerElementsAllowed === true && fieldData.type !== 'container') {
                    forcedDisabled = true;
                }

                if (outputWorkflowConfig.allowed_class_types.length === 0 || forcedDisabled === true) {
                    disabledClass = 'formbuilder-object-editor-disabled';
                }

                var item = {
                    text: fieldData['display_name'],
                    type: 'layout',
                    iconCls: fieldTypeConfig['icon_class'],
                    cls: disabledClass,
                    leaf: false,
                    allowDrag: false,
                    draggable: false,
                    expandable: false,
                    expanded: true,
                    omFieldDisabled: forcedDisabled,
                    omFieldTypeIdentifier: _.FIELD_TYPE_FORM_BUILDER,
                    omFieldAttributes: {
                        data: fieldData,
                        typeConfig: fieldTypeConfig
                    },
                    isAllowedInClassType: function (sourceNode) {
                        var pimcoreDataTypeConfig,
                            pimcoreDataType,
                            fieldData = _.buildFormFieldConfigFromNode({data: this});

                        if (fieldData === null) {
                            return false;
                        }

                        if (fieldData.outputWorkflowConfig === null) {
                            return false;
                        }

                        if (fieldData.omFieldIsDisabled === true) {
                            return false;
                        }

                        pimcoreDataTypeConfig = sourceNode.data;
                        pimcoreDataType = pimcoreDataTypeConfig.dataType;

                        return in_array(pimcoreDataType, fieldData.outputWorkflowConfig.availableClassTypes);
                    }
                };

                if (fieldData.hasOwnProperty('fields') && Ext.isArray(fieldData.fields) && fieldData.type === 'container') {
                    item.omContainerFields = fieldData.fields;
                    // allow all container types except repeater!
                    if (fieldData.sub_type !== 'repeater') {
                        item.children = generateFields(fieldData.fields, []);
                    }
                }

                // do not add any data to form item, if it has been forced disabled
                item = forcedDisabled ? item : _.resolveItemChildren(item);

                treeItems.push(item);

            }.bind(this));

            return treeItems;
        };

        treeItems = generateFields(this.formFieldDefinitions, []);

        this.formTreePanel = new Ext.tree.TreePanel({
            region: 'center',
            title: this.pimcoreClassType === 'object' ? 'Form Data' : 'Form Container Data',
            layout: 'fit',
            split: true,
            rootVisible: true,
            autoScroll: true,
            listeners: {
                itemcontextmenu: this.onTreeNodeContextmenu.bind(this)
            },
            root: {
                id: '0',
                fbType: 'root',
                fbTypeContainer: 'root',
                text: this.formRootName,
                iconCls: this.formRootIconCls,
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
                    sortable: false
                },
                {
                    xtype: 'actioncolumn',
                    text: t('configuration'),
                    sortable: false,
                    getClass: function (disabled, metaData, record) {

                        var fieldData = this.buildFormFieldConfigFromNode(record);

                        if (fieldData === null) {
                            return '';
                        }

                        if (fieldData.omFieldIsDisabled === true) {
                            return 'formbuilder-object-editor-grid-unavailable';
                        }

                        if (fieldData.omFieldTypeIdentifier !== this.FIELD_TYPE_DATA_CLASS_FIELD) {
                            return '';
                        }

                        if (fieldData.omFieldTypeNeedsConfiguration === false) {
                            return 'formbuilder-object-editor-grid-ok';
                        }

                        if (fieldData.omFieldConfigurationWorker.isReadyToConfigure(record) === false) {
                            return 'formbuilder-object-editor-grid-attention';
                        }

                        if (fieldData.omFieldConfigurationWorker.isValid(record) === false) {
                            return 'formbuilder-object-editor-grid-attention';
                        }

                        return 'formbuilder-object-editor-grid-ok-edit';

                    }.bind(this),
                    isDisabled: function (grid, rowIndex) {
                        var record = grid.getStore().getAt(rowIndex),
                            fieldData = this.buildFormFieldConfigFromNode(record);

                        if (fieldData === null) {
                            return false;
                        }

                        if (fieldData.omFieldTypeIdentifier !== this.FIELD_TYPE_DATA_CLASS_FIELD) {
                            return true;
                        }

                        if (fieldData.omFieldTypeNeedsConfiguration === false) {
                            return true;
                        }

                        return fieldData.omFieldConfigurationWorker.isReadyToConfigure(record) === false;

                    }.bind(this),
                    handler: function (grid, rowIndex) {

                        var record = grid.getStore().getAt(rowIndex),
                            fieldData = this.buildFormFieldConfigFromNode(record);

                        if (fieldData.omFieldTypeNeedsConfiguration === false) {
                            return;
                        }

                        fieldData.omFieldConfigurationWorker.getConfigDialog(record, function () {
                            grid.refresh();
                        });

                    }.bind(this)
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
                            record, copyData, copy;

                        record = data.records[0];

                        if (target !== source) {
                            if (this.formTreePanel.getRootNode().findChildBy(function (child) {
                                return child.data.omFieldTypeIdentifier === this.FIELD_TYPE_DATA_CLASS_FIELD && child.data.name === record.data.name;
                            }.bind(this), null, true)) {
                                dropHandlers.cancelDrop();
                            } else {
                                copyData = Ext.apply({}, record.data);
                                delete copyData.id;
                                copyData.omFieldTypeIdentifier = this.FIELD_TYPE_DATA_CLASS_FIELD;
                                copyData.omFieldConfigurationWorker = this.getWorkerByNode(record, copyData);
                                copyData.omFieldTypeNeedsConfiguration = copyData.omFieldConfigurationWorker !== null;

                                copy = record.createNode(copyData);
                                data.records = [copy];
                            }
                        }
                    }.bind(this),
                    nodedragover: function (targetNode, dropPosition, dragData) {

                        var sourceNode;

                        if (dropPosition !== 'append') {
                            return false;
                        }

                        sourceNode = dragData.records[0];

                        if (typeof targetNode.data.isAllowedInClassType !== 'function') {
                            return false;
                        }

                        return targetNode.data.isAllowedInClassType(sourceNode);

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

    onTreeNodeContextmenu: function (tree, record, item, index, ev) {

        var menu = new Ext.menu.Menu();

        ev.stopEvent();
        tree.select();

        if (!record.hasOwnProperty('data')) {
            return;
        }

        if (record.data.omFieldTypeIdentifier !== this.FIELD_TYPE_DATA_CLASS_FIELD) {
            return;
        }

        menu.add(new Ext.menu.Item({
            text: t('delete'),
            iconCls: 'pimcore_icon_delete',
            handler: function (record) {
                record.parentNode.removeChild(record, true);
            }.bind(this, record)
        }));

        menu.showAt(ev.pageX, ev.pageY);
    },

    getClassDefinitionTreePanel: function () {

        var classFetchUrl = this.pimcoreClassType === 'fieldcollection'
            ? '/admin/class/fieldcollection-get'
            : '/admin/class/get-class-definition-for-column-config';

        if (!this.classTreePanel) {
            this.classTreePanel = this.getClassTree(classFetchUrl, this.pimcoreClassId, 0);
        }

        this.classDefinitionTreePanel = new Ext.Panel({
            layout: 'fit',
            region: 'east',
            width: this.pimcoreClassType === 'object' ? 600 : 400,
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

    isValid: function () {

        this.formDataHasInvalidFields = false;

        if (!this.formTreePanel) {
            return false;
        }

        this.getFormFieldsRecursive(this.formTreePanel.getRootNode());

        if (this.formDataHasInvalidFields === true) {
            Ext.Msg.alert(t('error'), t('form_builder.output_workflow.output_workflow_channel.object.editor.invalid_configuration'));
            return false;
        }

        return true;
    },

    getEditorData: function () {

        var data;

        if (!this.formTreePanel) {
            return null;
        }

        data = this.getFormFieldsRecursive(this.formTreePanel.getRootNode());

        return data;
    },

    getFormFieldsRecursive: function (node) {

        var elements = [];

        node.eachChild(function (child) {

            var obj = {},
                config = {},
                fieldData = this.buildFormFieldConfigFromNode(child),
                omFieldTypeIdentifier = fieldData.omFieldTypeIdentifier;

            obj.type = omFieldTypeIdentifier;

            if (fieldData.omFieldTypeNeedsConfiguration === true) {
                if (fieldData.omFieldConfigurationWorker.isValid(node) === false) {
                    this.formDataHasInvalidFields = true;
                    return false;
                }
            }

            config.worker = null;
            config.workerData = null;

            if (omFieldTypeIdentifier === this.FIELD_TYPE_FORM_BUILDER) {
                obj.fieldType = fieldData.omFieldAttributesData.type === 'container' ? ('container_' + fieldData.omFieldAttributesTypeConfig.id) : fieldData.omFieldAttributesTypeConfig.type;
                config = {name: fieldData.omFieldAttributesData.name}
            } else if (omFieldTypeIdentifier === this.FIELD_TYPE_DATA_CLASS_FIELD) {
                obj.fieldType = child.data.dataType;
                config = {name: child.data.name}
            }

            if (fieldData.omFieldTypeNeedsConfiguration === true) {
                config.worker = fieldData.omFieldConfigurationWorker.getName();
                config.workerData = fieldData.omFieldConfigurationWorker.getData();
            }

            obj.config = config;
            obj.childs = this.getFormFieldsRecursive(child);

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

            if (fieldData.hasOwnProperty('childs') && Ext.isArray(fieldData.childs) && fieldData.childs.length > 0) {
                d = this.findStoredConfigNodeData(item, fieldData.childs);
                if (d !== null) {
                    return false;
                }
            }
        }.bind(this));

        return d;
    },

    addConfigurationNodesToItem: function (item, correspondingConfigNodes) {

        Ext.Array.each(correspondingConfigNodes, function (subFieldData) {

            var subItem = null,
                record = null,
                copyData,
                type = subFieldData.type,
                fieldType = subFieldData.fieldType,
                fieldTypeConfig = subFieldData.config,
                fieldWorker = fieldTypeConfig.hasOwnProperty('worker') ? fieldTypeConfig.worker : null;

            if (type === this.FIELD_TYPE_DATA_CLASS_FIELD) {
                record = this.classTreePanel.getRootNode().findChild('key', fieldTypeConfig.name, true);
                if (record) {
                    copyData = Ext.apply({}, record.data);
                    delete copyData.id;
                    copyData.omFieldTypeIdentifier = this.FIELD_TYPE_DATA_CLASS_FIELD;
                    copyData.omFieldTypeNeedsConfiguration = false;
                    subItem = record.createNode(copyData);
                } else {
                    Ext.Msg.alert(t('error'), 'Cannot create data class element "' + type + '" (' + fieldType + ').');
                    return false;
                }
            }

            if (subItem === null) {
                return;
            }

            if (fieldWorker !== null) {
                subItem.data.omFieldConfigurationWorker = this.getWorker(fieldWorker, subItem.data.name, fieldTypeConfig.workerData);
                subItem.data.omFieldTypeNeedsConfiguration = true;
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
    },

    getWorkerByNode: function (record, attr) {

        if (attr.dataType === 'manyToOneRelation' || attr.dataType === 'manyToManyRelation') {
            return this.getWorker('relationWorker', attr.name, null);
        }

        if (attr.dataType === 'fieldcollections') {
            return this.getWorker('fieldCollectionWorker', attr.name, null);
        }

        return null;
    },

    getWorker: function (workerName, correspondingFieldName, data) {
        return new Formbuilder.extjs.extensions.formObjectMappingEditorWorker[workerName](this.formId, this.pimcoreClassId, correspondingFieldName, data);
    }
});
