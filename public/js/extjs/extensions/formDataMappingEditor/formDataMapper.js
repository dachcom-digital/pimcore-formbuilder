pimcore.registerNS('Formbuilder.extjs.extensions.formDataMappingEditor.formDataMapper');
Formbuilder.extjs.extensions.formDataMappingEditor.formDataMapper = Class.create({

    formId: null,
    formRootName: null,
    formRootIconCls: null,
    editorData: null,
    apiProviderData: null,
    formFieldDefinitions: null,
    fieldTransformer: null,
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
        this.fieldTransformer = configuration.fieldTransformer
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

    filterStore: function (store, storeCollection, additionalAllowedEntities) {

        store.clearFilter();

        store.filterBy(function (rec) {

            var isNotInStoreCollection = storeCollection.findIndexBy(function (item) {
                return item.get('value') === rec.get('value');
            }) === -1

            Ext.Array.each(additionalAllowedEntities, function (additionalAllowedEntity) {
                if (rec.get('value') === additionalAllowedEntity) {
                    isNotInStoreCollection = true;
                }
            });

            return isNotInStoreCollection === true;
        });
    },

    getFormTreePanel: function () {

        var treeItems,
            hasPredefinedApiFields = this.apiProviderData.predefinedApiFields.length > 0,
            predefinedApiFields = this.apiProviderData.predefinedApiFields,
            predefinedApiFieldStore = new Ext.data.Store({
                fields: ['label', 'value'],
                data: predefinedApiFields === null ? [] : predefinedApiFields,
            }),
            fieldTransformerStore = new Ext.data.Store({
                fields: ['label', 'value', 'description'],
                data: this.fieldTransformer,
                listeners: {
                    load: function (store) {

                        if (store.getCount() === 0) {
                            return;
                        }

                        store.insert(0, new Ext.data.Record({label: 'None', value: null, description: null}));

                    }
                }
            }),
            storeCollection = new Ext.util.Collection(),
            generateFields = function (fields, treeItems, parent) {

                Ext.Array.each(fields, function (field) {

                    var fieldData = field.data,
                        fieldTypeConfig = field.type,
                        editorData = this.editorData,
                        apiMappingValues = this.findValue('apiMapping', editorData, fieldData.name),
                        fieldTransformer = this.findValue('fieldTransformer', editorData, fieldData.name),
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
                            apiMapping: Ext.isArray(apiMappingValues) ? Ext.Array.map(apiMappingValues, function (value) {
                                return predefinedApiFieldStore.findRecord('value', Ext.isObject(value) ? value.get('value') : value);
                            }) : [],
                            fieldTransformer: fieldTransformer,
                            formFieldAttributes: {
                                fieldData: fieldData
                            }
                        };

                    Ext.Array.each(Ext.isArray(apiMappingValues) ? apiMappingValues : [], function (mappingKey) {
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
                    clicksToEdit: 1,
                    listeners: {
                        beforeedit: function (editor, context) {

                            var additionalAllowedEntities = [];
                            if (Ext.isArray(context.record.get('apiMapping'))) {
                                Ext.Array.each(context.record.get('apiMapping'), function (record) {
                                    additionalAllowedEntities.push((Ext.isObject(record) ? record.get('value') : record));
                                })
                            }

                            this.filterStore(predefinedApiFieldStore, storeCollection, additionalAllowedEntities);

                        }.bind(this),
                        validateedit: function() {
                            predefinedApiFieldStore.clearFilter();
                        }
                    }
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
                    editable: true,
                    itemId: Ext.id(),
                    getEditor: function (ev) {

                        var editor;

                        if (ev.id === '0') {
                            return false;
                        }

                        editor = new Ext.form.field.Tag({
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

                                    if (Ext.isArray(record)) {
                                        Ext.Array.each(record, function (singleRecord) {
                                            storeCollection.add(singleRecord);
                                        });
                                    } else {
                                        storeCollection.add(record);
                                    }

                                    this.filterStore(predefinedApiFieldStore, storeCollection, []);
                                }.bind(this),
                                beforedeselect: function (ev, record) {
                                    storeCollection.remove(record);
                                    this.filterStore(predefinedApiFieldStore, storeCollection, []);
                                }.bind(this)
                            }
                        });

                        return editor;

                    }.bind(this),
                    renderer: function (v, cell, record) {

                        var storeRecord;

                        if (record.get('id') === '0') {
                            return null;
                        }

                        if (Ext.isArray(v)) {

                            return Ext.Array.merge(
                                [],
                                ...Ext.Array.map(v, function (value) {

                                    var storeRecord;

                                    if (Ext.isObject(value)) {
                                        return value.get('label');
                                    }

                                    storeRecord = predefinedApiFieldStore.findRecord('value', value);

                                    return storeRecord ? storeRecord.get('label') : v;
                                })
                            ).join(', ');
                        }

                        if (Ext.isObject(v)) {
                            return v.get('label');
                        }

                        storeRecord = predefinedApiFieldStore.findRecord('value', v);

                        return storeRecord ? storeRecord.get('label') : v;

                    }.bind(this)
                },
                {
                    text: 'Transformer',
                    dataIndex: 'fieldTransformer',
                    flex: 2,
                    sortable: false,
                    hidden: this.fieldTransformer.length === 0,
                    getEditor: function (ev) {

                        var editor, formFieldAttributes = ev.get('formFieldAttributes');

                        if (ev.id === '0') {
                            return false;
                        }

                        if (formFieldAttributes.fieldData.type === 'container') {
                            return false;
                        }

                        editor = Ext.create('Ext.form.ComboBox', {
                            queryDelay: 0,
                            displayField: 'label',
                            valueField: 'value',
                            anchor: '100%',
                            store: fieldTransformerStore,
                            allowBlank: true,
                            editable: false,
                            selectOnFocus: false,
                            forceSelection: true
                        });

                        return editor;

                    }.bind(this),
                    renderer: function (v, cell, ev) {

                        var record;

                        if (ev.id === '0') {
                            return null;
                        }

                        record = fieldTransformerStore.findRecord('value', v);

                        if (record === null) {
                            return null;
                        }

                        if (record.get('description') !== null) {
                            cell['tdAttr'] = 'data-qtip="' + record.get('description') + '"';
                        }

                        return record.get('label');
                    }
                }
            ]
        });

        return this.formTreePanel;
    },

    findValue: function (identifier, editorData, fieldName) {

        var value = null;

        if (!Ext.isArray(editorData)) {
            return value;
        }

        Ext.Array.each(editorData, function (field) {

            if (field.name === fieldName) {
                value = field.config[identifier]

                return false;
            }

            if (field.hasOwnProperty('children')) {
                value = this.findValue(identifier, field.children, fieldName);
                if (value !== null) {
                    return false;
                }
            }

        }.bind(this));

        return value;
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
                children = this.getFormFieldsRecursive(child),
                apiMapping = [];

            if (Ext.isArray(child.get('apiMapping'))) {
                Ext.Array.each(child.get('apiMapping'), function (record) {
                    apiMapping.push(Ext.isObject(record) ? record.get('value') : record);
                })
            } else if (Ext.isObject(child.get('apiMapping'))) {
                apiMapping.push(child.get('value'));
            } else if (Ext.isString(child.get('apiMapping'))) {
                apiMapping.push(child.get('apiMapping'));
            }

            obj = {
                name: formFieldAttributes.fieldData.name,
                config: {
                    apiMapping: apiMapping,
                    fieldTransformer: child.get('fieldTransformer') === undefined ? null : child.get('fieldTransformer'),
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