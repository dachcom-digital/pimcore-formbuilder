pimcore.registerNS('Formbuilder.extjs.extensions.formDataMappingEditor.formDataMapper');
Formbuilder.extjs.extensions.formDataMappingEditor.formDataMapper = Class.create({

    formId: null,
    formRootName: null,
    formRootIconCls: null,
    editorData: null,
    apiProviderData: null,
    formFieldDefinitions: null,
    formDataHasInvalidFields: false,
    editPanel: null,
    formTreePanel: null,

    initialize: function (formId, editorData, configuration, formRootName, formRootIconCls) {
        this.formId = formId;
        this.formRootName = formRootName ? formRootName : t('form_builder_base');
        this.formRootIconCls = formRootIconCls ? formRootIconCls : 'form_builder_icon_root';
        this.editorData = editorData;
        this.apiProviderData = configuration.apiProvider;
        this.formFieldDefinitions = configuration.formFieldDefinitions
        this.formDataHasInvalidFields = false;
    },

    getLayout: function () {

        this.editPanel = new Ext.Panel({
            layout: 'form',
            region: 'north',
            autoScroll: true,
            border: false
        });

        this.editPanel.add(this.getFormTreePanel());

        return this.editPanel;
    },

    findApiMappingValue: function (editorData, fieldName) {

        var apiMapping = [];

        if (!Ext.isArray(editorData)) {
            return apiMapping;
        }

        Ext.Array.each(editorData, function (field) {

            if (field.name === fieldName) {
                apiMapping = field.config.apiMapping

                return false;
            }

            if (field.hasOwnProperty('children')) {
                apiMapping = this.findApiMappingValue(field.children, fieldName);
                if (apiMapping.length > 0) {
                    return false;
                }
            }

        }.bind(this));

        return apiMapping;
    },

    getFormTreePanel: function () {

        var treeItems,
            hasPredefinedApiFields = this.apiProviderData.predefinedApiFields.length > 0,
            predefinedApiFields = this.apiProviderData.predefinedApiFields,
            predefinedApiFieldStore = new Ext.data.Store({
                fields: ['label', 'value'],
                data: predefinedApiFields === null ? [] : predefinedApiFields,
            }),
            storeCollection = new Ext.util.Collection(),
            generateFields = function (fields, treeItems, parent) {

                Ext.Array.each(fields, function (field) {

                    var fieldData = field.data,
                        fieldTypeConfig = field.type,
                        editorData = this.editorData,
                        apiMappingValues = this.findApiMappingValue(editorData, fieldData.name),
                        item = {
                            type: 'layout',
                            text: fieldData.display_name,
                            iconCls: fieldTypeConfig.icon_class,
                            leaf: false,
                            allowDrag: false,
                            draggable: false,
                            expandable: false,
                            expanded: true,
                            children: [],
                            apiMapping: apiMappingValues,
                            formFieldAttributes: {
                                fieldData: fieldData
                            }
                        };

                    Ext.Array.each(apiMappingValues, function (mappingKey) {
                        var record = predefinedApiFieldStore.findRecord('value', mappingKey);
                        if (record !== null) {
                            storeCollection.add(predefinedApiFieldStore.findRecord('value', mappingKey));
                            predefinedApiFieldStore.getData().onFilterChange();
                        }
                    });

                    if (parent !== null) {
                        parent.children.push(item);
                    } else {
                        treeItems.push(item);
                    }

                    if (fieldData.hasOwnProperty('fields') && Ext.isArray(fieldData.fields) && fieldData.type === 'container') {
                        generateFields(fieldData.fields, treeItems, item);
                    }

                }.bind(this));

                return treeItems;
            }.bind(this);

        predefinedApiFieldStore.filter(new Ext.util.Filter({
            filterFn: function (rec) {
                return storeCollection.findIndexBy(function (item) {
                    return item.get('value') === rec.get('value');
                }) === -1;
            }
        }));

        treeItems = generateFields(this.formFieldDefinitions, [], null);

        this.formTreePanel = new Ext.tree.TreePanel({
            region: 'center',
            title: false,
            layout: 'fit',
            split: true,
            rootVisible: true,
            autoScroll: true,
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
            plugins: [
                Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 1
                })
            ],
            columns: [
                {
                    xtype: 'treecolumn',
                    text: 'Form Fields',
                    dataIndex: 'text',
                    flex: 2,
                    sortable: false
                },
                {
                    text: 'API Fields',
                    dataIndex: 'apiMapping',
                    flex: 2,
                    sortable: false,
                    getEditor: function (ev) {

                        var editor;

                        if (ev.id === '0') {
                            return false;
                        }

                        editor = Ext.create('Ext.form.field.Tag', {
                            queryDelay: 0,
                            displayField: 'label',
                            valueField: 'value',
                            anchor: '100%',
                            store: predefinedApiFieldStore,
                            allowBlank: true,
                            editable: true,
                            createNewOnBlur: !hasPredefinedApiFields,
                            createNewOnEnter: !hasPredefinedApiFields,
                            selectOnFocus: false,
                            forceSelection: true,
                            listeners: {
                                select: function (ev, record) {
                                    storeCollection.add(record);
                                    predefinedApiFieldStore.getData().onFilterChange();
                                },
                                beforedeselect: function (ev, record) {
                                    storeCollection.remove(record);
                                    predefinedApiFieldStore.getData().onFilterChange();
                                }
                            }
                        });

                        return editor;

                    }.bind(this),
                    renderer: function (v, cell, ev) {

                        if (ev.id === '0') {
                            return null;
                        }

                        if (Ext.isArray(v)) {
                            return v.join(', ');
                        }

                        return v;

                    }.bind(this)
                }
            ]
        });

        return this.formTreePanel;
    },

    isValid: function () {

        this.formDataHasInvalidFields = false;

        if (!this.formTreePanel) {
            return false;
        }

        this.getFormFieldsRecursive(this.formTreePanel.getRootNode());

        if (this.formDataHasInvalidFields === true) {
            Ext.Msg.alert(t('error'), t('form_builder.output_workflow.output_workflow_channel.api.editor.invalid_configuration'));
            return false;
        }

        return true;
    },

    getEditorData: function () {

        if (!this.formTreePanel) {
            return null;
        }

        return this.getFormFieldsRecursive(this.formTreePanel.getRootNode());
    },

    getFormFieldsRecursive: function (node) {

        var elements = [];

        node.eachChild(function (child) {

            var obj,
                formFieldAttributes = child.get('formFieldAttributes'),
                children = this.getFormFieldsRecursive(child);

            obj = {
                name: formFieldAttributes.fieldData.name,
                config: {
                    apiMapping: child.get('apiMapping') === undefined ? [] : child.get('apiMapping')
                }
            }

            if (children.length > 0) {
                obj.children = children;
            }

            elements.push(obj);

        }.bind(this));

        return elements;
    }
});