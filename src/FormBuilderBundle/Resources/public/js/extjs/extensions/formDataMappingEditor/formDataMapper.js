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
    formApiConfigurationPanel: null,

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

        if (this.apiProviderData.configurationFields.length > 0) {
            this.editPanel.add(this.getApiConfigurationPanel());
        }

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

    getApiConfigurationPanel: function () {

        var configurationFields = [],
            storedConfiguration = this.editorData !== null ? this.editorData.configuration : null

        Ext.Array.each(this.apiProviderData.configurationFields, function (configRow) {

            var value = storedConfiguration !== null && storedConfiguration.hasOwnProperty(configRow.name) ? storedConfiguration[configRow.name] : null;

            switch (configRow.type) {
                case 'text' :
                    configurationFields.push({
                        xtype: 'textfield',
                        name: configRow['name'],
                        fieldLabel: configRow['label'],
                        allowBlank: configRow['required'] === false,
                        value: value
                    });
                    break;
                case 'select' :
                    configurationFields.push({
                        xtype: 'combobox',
                        name: configRow['name'],
                        fieldLabel: configRow['label'],
                        allowBlank: configRow['required'] === false,
                        store: new Ext.data.Store({
                            fields: ['label', 'value'],
                            data: configRow['store']
                        }),
                        value: value,
                        queryDelay: 0,
                        displayField: 'label',
                        valueField: 'value',
                        mode: 'local',
                        editable: false,
                        triggerAction: 'all',
                    });
                    break;
            }
        }.bind(this));

        this.formApiConfigurationPanel = new Ext.form.FormPanel({
            title: false,
            border: false,
            items: configurationFields
        });

        return this.formApiConfigurationPanel;
    },

    getFormTreePanel: function () {

        var treeItems,
            generateFields = function (fields, treeItems, parent) {

                Ext.Array.each(fields, function (field) {

                    var fieldData = field.data,
                        fieldTypeConfig = field.type,
                        editorData = this.editorData !== null ? this.editorData.fields : null,
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
                            apiMapping: this.findApiMappingValue(editorData, fieldData.name),
                            formFieldAttributes: {
                                fieldData: fieldData
                            }
                        };

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

                        var editor, predefinedApiFields = null;

                        if (ev.id === '0') {
                            return false;
                        }

                        if (this.apiProviderData.predefinedApiFields.length > 0) {
                            predefinedApiFields = this.apiProviderData.predefinedApiFields;
                        }

                        editor = Ext.create('Ext.form.field.Tag', {
                            queryDelay: 0,
                            displayField: 'label',
                            valueField: 'value',
                            anchor: '100%',
                            store: new Ext.data.Store({
                                fields: ['label', 'value'],
                                data: predefinedApiFields === null ? [] : predefinedApiFields
                            }),
                            allowBlank: true,
                            editable: true,
                            createNewOnBlur: predefinedApiFields === null,
                            createNewOnEnter: predefinedApiFields === null,
                            selectOnFocus: false,
                            filterPickList: true,
                            forceSelection: predefinedApiFields !== null,
                            hideTrigger: true,
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

        if (this.formApiConfigurationPanel !== null) {
            console.warn(this.formApiConfigurationPanel.form.isValid());
            this.formDataHasInvalidFields = !this.formApiConfigurationPanel.form.isValid();
        }

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

        return {
            configuration: this.formApiConfigurationPanel !== null ? this.formApiConfigurationPanel.form.getValues() : null,
            fields: this.getFormFieldsRecursive(this.formTreePanel.getRootNode())
        };
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