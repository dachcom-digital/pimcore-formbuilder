pimcore.registerNS('Formbuilder.extjs.formPanel.panel.config');
Formbuilder.extjs.formPanel.config = Class.create({

    formSelectionPanel: null,
    parentPanel: null,
    panel: null,
    editPanel: null,
    tree: null,

    getDataSuccess: true,
    availableFormFields: [],
    availableContainerTypes: [],
    formId: null,
    formName: null,
    formMeta: {},
    formHasOutputWorkflows: false,
    formConfig: null,
    formConfigStore: {},
    formConditionalsStructured: {},
    formConditionalsStore: {},
    formFields: null,
    allowedMoveElements: {
        'root': [
            'field',
            'container'
        ],
        'field': [
            'constraint'
        ],
        'container': [
            'field'
        ],
        'constraint': []
    },
    formValidator: {
        'root': [],
        'fields': {}
    },

    initialize: function (formData, formSelectionPanel) {
        this.formSelectionPanel = formSelectionPanel;
        this.formId = formData.id;
        this.formName = formData.name;
        this.formGroup = formData.group;
        this.formMeta = formData.meta;
        this.formHasOutputWorkflows = formData.has_output_workflows;
        this.formConfig = formData.config.length === 0 ? {} : formData.config;
        this.formConfigStore = formData.config_store;
        this.formConditionalsStructured = formData.conditional_logic;
        this.formConditionalsStore = formData.conditional_logic_store;
        this.formFields = formData.fields;
        this.doubleOptIn = formData.double_opt_in;
        this.availableFormFields = formData.fields_structure;
        this.availableContainerTypes = formData.container_types;
        this.availableConstraints = formData.validation_constraints;
        this.availableFormFieldTemplates = formData.fields_template;
    },

    getLayout: function (parentPanel) {

        this.parentPanel = parentPanel;

        this.tree = Ext.create('Ext.tree.Panel', {
            region: 'west',
            autoScroll: true,
            listeners: this.getTreeNodeListeners(),
            animate: false,
            split: true,
            enableDD: true,
            width: 300,
            cls: 'form-builder-form-elements-tree',
            root: {
                id: '0',
                fbType: 'root',
                fbTypeContainer: 'root',
                text: t('form_builder_base'),
                iconCls: 'form_builder_icon_root',
                isTarget: true,
                leaf: true,
                root: true
            },
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    ddGroup: 'element'
                }
            }
        });

        this.editPanel = new Ext.Panel({
            region: 'center',
            bodyStyle: 'padding: 10px;',
            cls: 'form-builder-form-configuration-panel',
            autoScroll: true
        });

        this.panel = new Ext.Panel({
            title: t('form_builder.tab.form_configuration'),
            closable: false,
            iconCls: 'form_builder_icon_form_configuration',
            autoScroll: true,
            border: false,
            layout: 'border',
            buttons: [
                {
                    text: t('import'),
                    iconCls: 'pimcore_icon_import',
                    handler: this.showImportPanel.bind(this)
                },
                {
                    text: t('export'),
                    iconCls: 'pimcore_icon_export',
                    handler: this.exportForm.bind(this)
                },
                {
                    text: t('save'),
                    iconCls: 'pimcore_icon_save',
                    handler: this.save.bind(this)
                }
            ],
            items: [this.tree, this.editPanel]
        });

        this.panel.on('beforedestroy', function () {
            if (this.formId) {
                this.editPanel.removeAll();
            }
        }.bind(this));

        this.setCurrentNode('root');
        this.initLayoutFields();

        return this.panel;

    },

    remove: function () {
        this.panel.destroy();
    },

    resetLayout: function () {
        this.tree.getRootNode().removeAll();
    },

    initLayoutFields: function () {

        if (!this.formFields) {
            return;
        }

        Ext.Array.each(this.formFields, function (formField) {
            var type = formField.hasOwnProperty('type') && formField.type === 'container' ? 'container' : 'formType',
                node = this.recursiveAddNode(this.tree.getRootNode(), formField, type);
            if (node !== null) {
                this.tree.getRootNode().appendChild(node);
            }
        }.bind(this));

        // expand root node by default
        this.tree.getRootNode().expand();

        // select root node "base"
        this.tree.getSelectionModel().select(this.tree.getRootNode(), true);

    },

    recursiveAddNode: function (scope, formTypeValues, type) {

        var nodeObject,
            newNode = null;

        if (type === 'formType') {
            nodeObject = this.getFormTypeStructure(formTypeValues.type);
            if (nodeObject === false) {
                Ext.MessageBox.alert(t('error'), 'Form type structure for type "' + formTypeValues.type + '" not found.');
                return null;
            }
            newNode = this.createFormField(scope, nodeObject, formTypeValues);

        } else if (type === 'container') {
            nodeObject = this.getContainerTypeStructure(formTypeValues.sub_type);
            if (nodeObject === false) {
                Ext.MessageBox.alert(t('error'), 'Form container for type "' + formTypeValues.sub_type + '" not found.');
                return null;
            }
            newNode = this.createContainerField(scope, nodeObject, formTypeValues);

        } else if (type === 'constraint') {
            nodeObject = this.getFormTypeConstraintStructure(formTypeValues.type);
            if (nodeObject === false) {
                Ext.MessageBox.alert(t('error'), 'Form constraint structure for type "' + formTypeValues.type + '" not found.');
                return null;
            }
            newNode = this.createFormFieldConstraint(scope, nodeObject, formTypeValues);
        }

        if (formTypeValues.hasOwnProperty('constraints') && Ext.isArray(formTypeValues.constraints)) {
            Ext.Array.each(formTypeValues.constraints, function (constraint) {
                this.recursiveAddNode(newNode, constraint, 'constraint');
            }.bind(this));
        }

        if (formTypeValues.hasOwnProperty('fields') && Ext.isArray(formTypeValues.fields)) {
            Ext.Array.each(formTypeValues.fields, function (field) {
                this.recursiveAddNode(newNode, field, 'formType');
            }.bind(this));
        }

        return newNode;
    },

    getTreeNodeListeners: function () {

        return {
            beforeselect: this.onTreeNodeBeforeSelect.bind(this),
            select: this.onTreeNodeSelect.bind(this),
            itemcontextmenu: this.onTreeNodeContextMenu.bind(this),
            beforeitemmove: this.onTreeNodeBeforeMove.bind(this)
        };
    },

    onTreeNodeBeforeMove: function (node, oldParent, newParent) {

        var targetType = newParent.data.fbType,
            elementType = node.data.fbType;

        if (node.get('fbSensitiveLocked') === true) {
            return oldParent === newParent;
        }

        return Ext.Array.contains(this.allowedMoveElements[targetType], elementType);
    },

    onTreeNodeBeforeSelect: function (tree) {
        try {
            this.storeCurrentNodeData();
        } catch (e) {
            Ext.MessageBox.alert(t('error'), e);
            return false;
        }
    },

    onTreeNodeSelect: function (tree, record) {

        var fbObject, displayField;

        this.editPanel.removeAll();

        if (record.data.fbType === 'root') {
            this.editPanel.add(this.getRootPanel());
            this.setCurrentNode('root');
            // could be field constraint, ...
        } else if (record.getData().hasOwnProperty('object')) {
            fbObject = record.getData().object;
            if (fbObject.storeData.locked) {
                return;
            }

            this.editPanel.add(fbObject.renderLayout());
            this.setCurrentNode(record.getData());

            // focus on display_name if available
            displayField = fbObject.form.getForm().findField('display_name');
            if (displayField !== null) {
                displayField.focus(true, true);
            }

            var nameField = fbObject.form.getForm().findField('name');
            if (nameField !== null) {
                if (record.get('fbSensitiveLocked') === true) {
                    nameField.setReadOnly(true);
                }
            }
        }

        this.editPanel.updateLayout();
    },

    onTreeNodeContextMenu: function (tree, record, item, index, ev) {

        var _ = this,
            parentType = record.data.fbType,
            deleteAllowed,
            showPaste = false,
            menu = new Ext.menu.Menu(),
            layoutElem = [];

        menu.on('hide', function (menu) {
            menu.destroy()
        }, this, {delay: 200});

        ev.stopEvent();

        this.tree.getSelectionModel().select(record, true);

        deleteAllowed = parentType !== 'root';
        if (record.data.object && record.data.object.storeData.locked) {
            deleteAllowed = false;
        }

        if (record.get('fbSensitiveLocked') === true) {
            deleteAllowed = false;
        }

        // add form items
        if (in_array(parentType, ['root', 'container']) && this.availableFormFields.length > 0) {

            Ext.Array.each(this.availableFormFields, function (formGroup) {

                var formGroupElements = [];

                if (formGroup.fields.length === 0) {
                    return true;
                }

                Ext.Array.each(formGroup.fields, function (formGroupElement, groupI) {
                    formGroupElements.push({
                        text: formGroupElement.label,
                        iconCls: formGroupElement.icon_class,
                        handler: this.createFormField.bind(this, record, formGroupElement, null, true)
                    });
                }.bind(this));

                layoutElem.push(new Ext.menu.Item({
                    text: formGroup.label,
                    iconCls: formGroup.icon_class,
                    hideOnClick: false,
                    menu: formGroupElements
                }));

            }.bind(this));

            menu.add(new Ext.menu.Item({
                text: t('form_builder_add_form_item'),
                iconCls: 'form_builder_icon_item_add',
                hideOnClick: false,
                menu: layoutElem
            }));

        }

        // constraint menu
        if (parentType === 'field' && record.data.object.allowedConstraints.length > 0) {

            var constraintElements = [];
            Ext.each(record.data.object.allowedConstraints, function (constraintId) {
                var constraint = _.getFormTypeConstraintStructure(constraintId);
                constraintElements.push(new Ext.menu.Item({
                    text: constraint.label,
                    iconCls: constraint.icon_class,
                    hideOnClick: true,
                    handler: _.createFormFieldConstraint.bind(_, record, constraint, null, true)
                }));
            });

            menu.add(new Ext.menu.Item({
                text: t('form_builder_add_validation'),
                iconCls: 'form_builder_icon_validation_add',
                hideOnClick: false,
                menu: constraintElements
            }));
        }

        // container menu
        if (parentType === 'root' && this.availableContainerTypes.length > 0) {

            var containerElements = [];
            Ext.Array.each(this.availableContainerTypes, function (container) {
                containerElements.push(new Ext.menu.Item({
                    text: container.label,
                    iconCls: container.icon_class,
                    hideOnClick: true,
                    handler: _.createContainerField.bind(_, record, container, null, true)
                }));
            });

            menu.add(new Ext.menu.Item({
                text: t('form_builder_add_container_type'),
                iconCls: 'form_builder_icon_container_type_add',
                hideOnClick: false,
                menu: containerElements
            }));

        }

        // copy menu
        if (parentType !== 'root') {
            menu.add(new Ext.menu.Item({
                text: t('copy'),
                iconCls: "pimcore_icon_copy",
                hideOnClick: true,
                handler: this.copyNode.bind(this, tree, record)
            }));
        }

        // paste menu
        if (pimcore && pimcore.formBuilderEditor && pimcore.formBuilderEditor.clipboard) {
            var copiedNodeType = pimcore.formBuilderEditor.clipboard.data.fbType;

            if (copiedNodeType === 'container' && parentType !== 'container' && parentType !== 'field' && parentType !== 'constraint') {
                showPaste = true;
            }
            if (copiedNodeType === 'field' && parentType !== 'field') {
                showPaste = true;
            }
            if (copiedNodeType === 'constraint' && parentType === 'field') {
                showPaste = true;
            }
            if (showPaste) {
                menu.add(new Ext.menu.Item({
                    text: t('paste'),
                    iconCls: "pimcore_icon_paste",
                    hideOnClick: true,
                    handler: this.dropNode.bind(this, tree, record)
                }));
            }
        }

        // delete menu
        if (deleteAllowed) {
            menu.add(new Ext.menu.Item({
                text: t('delete'),
                iconCls: 'pimcore_icon_delete',
                handler: this.removeFormField.bind(this, tree, record)
            }));
        }

        menu.showAt(ev.pageX, ev.pageY);
    },

    copyNode: function (tree, record) {
        if (!pimcore.formBuilderEditor) {
            pimcore.formBuilderEditor = {};
        }

        pimcore.formBuilderEditor.clipboard = this.cloneChild(tree, record);
    },

    dropNode: function (tree, record) {
        var node = pimcore.formBuilderEditor.clipboard;
        var newNode = this.cloneChild(tree, node);

        record.appendChild(newNode);
        tree.updateLayout();
        tree.getSelectionModel().select(newNode, true);
    },

    setCurrentNode: function (currentNode) {
        this.currentNode = currentNode;
    },

    storeCurrentNodeData: function () {

        if (!this.currentNode) {
            return;
        }

        if (this.currentNode === 'root') {
            this.dispatchRootNodeValueParser();
        } else {
            this.dispatchFieldNodeValueParser();
        }
    },

    /**
     * Store Root Panel Data
     */
    dispatchRootNodeValueParser: function () {

        var rootFields,
            formConditionals = {},
            formAttributes = {},
            parsedFormAttributes, items;

        if (this.rootPanel === undefined) {
            //root panel not initialized yet.
            return;
        }

        // setup validator
        rootFields = this.rootPanel.getForm().getFields();
        this.formValidator.root = [];

        if (rootFields.length > 0) {
            rootFields.each(function (field) {
                if (typeof field.getValue === 'function') {
                    if (field.allowBlank !== true &&
                        field.getXType() !== 'hiddenfield' && (
                            field.getValue() === null ||
                            field.getValue() === '' ||
                            (Ext.isArray(field.getValue()) && field.getValue().length === 0)
                        )
                    ) {
                        this.formValidator.root.push({name: field.getName(), message: field.getName() + ' cannot be empty.'});
                    }
                }
            }.bind(this));
        }

        items = this.rootPanel.queryBy(function (el) {
            return el.submitValue === undefined || el.submitValue === true;
        });

        Ext.Array.each(items, function (item, index) {
            if (typeof item.getValue === 'function') {

                var val = item.getValue(),
                    fieldName = item.name;

                if (fieldName) {

                    if (fieldName === 'name') {
                        this.formName = val;
                    }

                    if (fieldName.substring(0, 3) === 'cl.') {
                        formConditionals[fieldName] = val;
                    } else if (fieldName.substring(0, 11) === 'attributes.') {
                        formAttributes[fieldName] = val;
                    } else {
                        this.formConfig[fieldName] = val;
                    }
                }
            }
        }.bind(this));

        // parse form config
        this.formConfig = DataObjectParser.transpose(this.formConfig).data();

        if (this.formConfig.doubleOptIn && this.formConfig.doubleOptIn.enabled === false) {
            this.formConfig.doubleOptIn = {enabled: false}
        }

        // parse conditional logic to add them later again
        // and also to send them to server well formatted.
        this.formConditionalsStructured = DataObjectParser.transpose(formConditionals).data();

        // parse form attributes to add them later again
        // and also to send them to server well formatted.
        parsedFormAttributes = DataObjectParser.transpose(formAttributes).data();
        this.formConfig['attributes'] = parsedFormAttributes['attributes'] ? parsedFormAttributes['attributes'] : {};

    },

    /**
     * Store Current Node Data
     */
    dispatchFieldNodeValueParser: function () {

        var fieldId;

        if (typeof this.currentNode.object !== 'object') {
            return;
        }

        if (this.currentNode.hasOwnProperty('id')) {
            fieldId = this.currentNode.id;
        } else {
            fieldId = 'default';
        }

        this.currentNode.object.applyData();

        this.formValidator.fields[fieldId] = [];
        if (!this.currentNode.object.isValid()) {
            this.formValidator.fields[fieldId].push({
                'name': this.currentNode.object.getName(),
                'message': t('form_builder_form_type_invalid')
            });
            return false;
        }

        try {
            this.checkNodeHasUniqueName(this.currentNode);
        } catch (e) {
            this.formValidator.fields[fieldId].push({'name': this.currentNode.object.getName(), 'message': e});
        }
    },

    formIsValid: function () {

        this.storeCurrentNodeData();

        this.getDataSuccess = true;
        this.validateRootNode();

        if (this.getDataSuccess === true) {
            this.validateSubNodes();
        }

        return this.getDataSuccess;
    },

    validateRootNode: function () {

        this.tree.getRootNode().set('cls', '');

        if (this.formValidator.root.length > 0) {
            Ext.Array.each(this.formValidator.root, function (validation) {
                this.tree.getRootNode().set('cls', 'tree_node_error');
                this.getDataSuccess = false;
                Ext.MessageBox.alert('Root ' + t('error'), validation.message);
                return false;
            }.bind(this));
        }

        if (in_array(this.formName.toLowerCase(), this.formSelectionPanel.getConfig().forbidden_form_field_names)) {
            this.tree.getRootNode().set('cls', 'tree_node_error');
            this.getDataSuccess = false;
            Ext.MessageBox.alert(t('error'), '"' + this.formName.toLowerCase() + '" is a reserved name and cannot be used.');
        }
    },

    validateSubNodes: function (node) {

        // initially undefined, use root to start:
        if (node === undefined) {
            node = this.tree.getRootNode();
        }

        node.set('cls', Ext.isString(node.get('cls')) ? node.get('cls').replace('tree_node_error', '') : '');

        if (typeof node.data.object === 'object') {
            if (Ext.isArray(this.formValidator.fields[node.getId()]) && this.formValidator.fields[node.getId()].length > 0) {
                Ext.Array.each(this.formValidator.fields[node.getId()], function (validation) {
                    var nodeClass = node.get('cls');
                    node.set('cls', Ext.isString(nodeClass) ? nodeClass + ' tree_node_error' : 'tree_node_error');
                    this.getDataSuccess = false;
                    Ext.MessageBox.alert('Field ' + t('error'), validation.message);
                    return false;
                }.bind(this));
            }
        }

        if (node.hasOwnProperty('childNodes') && Ext.isArray(node.childNodes)) {
            Ext.Array.each(node.childNodes, function (childNode, i) {
                this.validateSubNodes(childNode);
            }.bind(this));
        }

    },

    getRootPanel: function () {

        var doubleOptInLocalizedField,
            methodStore = new Ext.data.ArrayStore({
                fields: ['value', 'label'],
                data: [['post', 'POST'], ['get', 'GET']]
            }),
            encStore = new Ext.data.ArrayStore({
                fields: ['value', 'label'],
                data: [
                    ['text/plain', 'text/plain'],
                    ['application/x-www-form-urlencoded', 'application/x-www-form-urlencoded'],
                    ['multipart/form-data', 'multipart/form-data']
                ]
            }),
            owStore = new Ext.data.Store({
                autoLoad: false,
                proxy: {
                    type: 'ajax',
                    url: '/admin/formbuilder/output-workflow/get-output-workflow-list/' + this.formId,
                    fields: ['id', 'name'],
                    reader: {
                        type: 'json',
                        rootProperty: 'outputWorkflows'
                    },
                },
                listeners: {
                    load: function (store) {
                        store.insert(0, {
                            id: 'all',
                            name: t('form_builder_email_csv_export_mail_type_all')
                        });
                    }
                }
            }),
            keyValueRepeater = new Formbuilder.extjs.types.keyValueRepeater(
                'attributes',
                t('form_builder_form_attribute_name') + ' & ' + t('form_builder_form_attribute_value'),
                this.formConfig['attributes'] ? this.formConfig['attributes'] : [],
                this.formConfigStore.attributes,
                false,
                false,
                false
            ),
            clBuilder = new Formbuilder.extjs.conditionalLogic.builder(this.formConditionalsStructured, this.formConditionalsStore, this);

        if (this.doubleOptIn.enabled === true) {

            doubleOptInLocalizedField = new Formbuilder.extjs.types.localizedField(function (locale) {

                var hrefField = new Formbuilder.extjs.types.href({
                        label: t('form_builder_form.double_opt_in.mail_template'),
                        id: 'doubleOptIn.mailTemplate.' + locale,
                        config: {
                            types: ['document'],
                            subtypes: {document: ['email']}
                        }
                    },
                    this.formConfig.doubleOptIn && this.formConfig.doubleOptIn.mailTemplate && this.formConfig.doubleOptIn.mailTemplate[locale]
                        ? this.formConfig.doubleOptIn.mailTemplate[locale]
                        : null,
                    null
                );

                return hrefField.getHref();

            }.bind(this), true);

            this.doubleOptInPanel = new Ext.form.FieldSet({
                title: t('form_builder_form.double_opt_in'),
                collapsible: false,
                autoHeight: true,
                width: '100%',
                style: 'margin-top: 20px;',
                submitValue: false,
                defaults: {
                    labelWidth: 160
                },
                items: [
                    {
                        xtype: 'checkbox',
                        name: 'doubleOptIn.enabled',
                        fieldLabel: t('form_builder_form.double_opt_in.enable'),
                        inputValue: true,
                        uncheckedValue: false,
                        labelWidth: 200,
                        value: this.formConfig.doubleOptIn ? this.formConfig.doubleOptIn.enabled : false,
                        listeners: {
                            change: function (cb, value) {

                                var containerField = cb.nextSibling();

                                containerField.setHidden(!value);
                                containerField.query('textfield[name="doubleOptIn.confirmationMessage"]')[0].allowBlank = !value

                            }.bind(this)
                        }
                    },
                    {
                        xtype: 'container',
                        hidden: !this.formConfig.doubleOptIn || this.formConfig.doubleOptIn.enabled === false,
                        defaults: {
                            labelWidth: 200
                        },
                        items: [
                            {
                                fieldLabel: false,
                                xtype: 'displayfield',
                                style: 'display:block !important; margin-bottom:15px !important; font-weight: 300;',
                                value: t('form_builder_form.double_opt_in.description')
                            },
                            {
                                xtype: 'checkbox',
                                name: 'doubleOptIn.allowMultipleUserSessions',
                                fieldLabel: t('form_builder_form.double_opt_in.allow_multiple_user_sessions'),
                                inputValue: true,
                                uncheckedValue: false,
                                value: this.formConfig.allowMultipleUserSessions ? this.formConfig.doubleOptIn.allowMultipleUserSessions : true,
                            },
                            {
                                xtype: 'textfield',
                                name: 'doubleOptIn.instructionNote',
                                fieldLabel: t('form_builder_form.double_opt_in.double_opt_in_instruction_note'),
                                value: this.formConfig.doubleOptIn ? this.formConfig.doubleOptIn.instructionNote : null,
                                allowBlank: true,
                                width: '100%',
                                inputAttrTpl: ' data-qwidth="250" data-qalign="br-r?" data-qtrackMouse="false" data-qtip="' + t('form_builder_type_field_base.translatable_field') + '"',
                            },
                            {
                                xtype: 'textfield',
                                name: 'doubleOptIn.confirmationMessage',
                                fieldLabel: t('form_builder_form.double_opt_in.confirmation_message'),
                                value: this.formConfig.doubleOptIn ? this.formConfig.doubleOptIn.confirmationMessage : null,
                                allowBlank: true,
                                width: '100%',
                                inputAttrTpl: ' data-qwidth="250" data-qalign="br-r?" data-qtrackMouse="false" data-qtip="' + t('form_builder_type_field_base.translatable_field') + '"',
                            },
                            doubleOptInLocalizedField.getField(),
                            {
                                xtype: 'button',
                                text: t('form_builder_form.double_opt_in.show_sessions'),
                                iconCls: 'pimcore_icon_export',
                                handler: this.showFormDoubleOptInData.bind(this)
                            }
                        ]
                    }
                ]
            });
        }

        this.metaDataPanel = keyValueRepeater.getRepeater();

        // add conditional logic field
        this.clBuilder = clBuilder.getLayout();

        // add export panel
        this.exportPanel = new Ext.form.FieldSet({
            title: t('form_builder_email_csv_export'),
            collapsible: false,
            autoHeight: true,
            width: '100%',
            style: 'margin-top: 20px;',
            submitValue: false,
            items: [
                {
                    xtype: 'combo',
                    fieldLabel: t('form_builder_email_csv_export_mail_type'),
                    queryDelay: 0,
                    displayField: 'name',
                    valueField: 'id',
                    mode: 'local',
                    labelAlign: 'top',
                    store: owStore,
                    value: 'all',
                    editable: false,
                    allowBlank: false,
                    submitValue: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    name: '_csvExportMailType',
                },
                {
                    xtype: 'toolbar',
                    style: 'margin-bottom: 5px;',
                    items: ['->', {
                        xtype: 'button',
                        text: t('export_csv'),
                        iconCls: 'pimcore_icon_export',
                        handler: this.exportFormEmailCsv.bind(this)
                    }]
                }]
        });

        this.rootPanel = new Ext.form.FormPanel({
            bodyStyle: 'padding: 10px',
            border: false,
            tbar: this.getRootPanelToolbar(),
            items: [
                {
                    xtype: 'textfield',
                    fieldLabel: t('form_builder_form_name'),
                    name: 'name',
                    width: 300,
                    value: this.formName,
                    allowBlank: false,
                    required: true
                },
                {
                    xtype: 'textfield',
                    fieldLabel: t('form_builder_form_group'),
                    name: 'group',
                    width: 300,
                    value: this.formGroup,
                    allowBlank: true,
                    required: false
                },
                {
                    xtype: 'textfield',
                    name: 'action',
                    value: this.formConfig.action ? this.formConfig.action : '/',
                    fieldLabel: t('form_builder_form_action'),
                    width: 300,
                    allowBlank: false
                },
                {
                    xtype: 'combo',
                    name: 'method',
                    fieldLabel: t('form_builder_form_method'),
                    queryDelay: 0,
                    displayField: 'label',
                    valueField: 'value',
                    mode: 'local',
                    store: methodStore,
                    editable: false,
                    triggerAction: 'all',
                    width: 300,
                    value: this.formConfig.method ? this.formConfig.method : 'POST',
                    allowBlank: false
                },
                {
                    xtype: 'combo',
                    name: 'enctype',
                    fieldLabel: t('form_builder_form_enctype'),
                    queryDelay: 0,
                    displayField: 'label',
                    valueField: 'value',
                    mode: 'local',
                    store: encStore,
                    editable: false,
                    triggerAction: 'all',
                    width: 300,
                    value: this.formConfig.enctype ? this.formConfig.enctype : 'multipart/form-data',
                    allowBlank: false
                },
                {
                    xtype: 'checkbox',
                    name: 'noValidate',
                    fieldLabel: t('form_builder_form_enable_html5_validation'),
                    value: this.formConfig.noValidate
                },
                {
                    xtype: 'checkbox',
                    name: 'useAjax',
                    fieldLabel: t('form_builder_form_ajax_submission'),
                    checked: this.formConfig.useAjax === undefined,
                    value: this.formConfig.useAjax
                },
                this.metaDataPanel,
                this.clBuilder,
                this.doubleOptInPanel ? this.doubleOptInPanel : null,
                this.exportPanel
            ]
        });

        return this.rootPanel;
    },

    getRootPanelToolbar: function () {

        var toolbar = new Ext.Toolbar(),
            items = [];

        items.push({
            xtype: 'tbtext',
            cls: 'x-panel-header-title-default',
            text: t('form_builder_form_configuration')
        });

        items.push('->');

        items.push({
            tooltip: t('show_metainfo'),
            iconCls: 'pimcore_icon_info',
            scale: 'medium',
            handler: this.showFormMetaInfo.bind(this)
        });

        toolbar.add(items);

        return toolbar;
    },

    showFormDoubleOptInData: function () {
        new Formbuilder.extjs.extensions.formDoubleOptInData(this.formId, this.formConfig.doubleOptIn);
    },

    showFormMetaInfo: function () {
        new Formbuilder.extjs.extensions.formMetaData(this.formId, this.formMeta);
    },

    createFormField: function (tree, formType, formTypeValues, selectNode) {

        var label = formTypeValues === null ? formType.label : formTypeValues.display_name,
            newNode = this.createFormFieldNode(label, formType.icon_class);

        newNode = tree.appendChild(newNode);

        if (formTypeValues !== null && formTypeValues.hasOwnProperty('name') && formTypeValues.name) {
            newNode.set('fbSensitiveFieldName', formTypeValues.name);
        }

        newNode.set(
            'object',
            new Formbuilder.extjs.components.formTypeBuilder(
                this, newNode, formType, this.availableFormFieldTemplates, formTypeValues
            )
        );

        tree.expand();

        if (selectNode === true) {
            this.tree.getSelectionModel().select(newNode, true);
        }

        return newNode;
    },

    createFormFieldNode: function (nodeLabel, iconCls) {
        return {
            text: nodeLabel,
            type: 'layout',
            draggable: true,
            iconCls: iconCls,
            fbType: 'field',
            fbTypeContainer: 'fields',
            leaf: false,
            expandable: false,
            expanded: true
        };
    },

    createFormFieldConstraint: function (tree, constraint, constraintValues, selectNode) {

        var newNode = this.createFormFieldConstraintNode(constraint.label, constraint.icon_class);

        newNode = tree.appendChild(newNode);
        newNode.set(
            'object',
            new Formbuilder.extjs.components.formFieldConstraint(
                this, newNode, constraint, constraintValues
            )
        );

        tree.expand();

        if (selectNode === true) {
            this.tree.getSelectionModel().select(newNode, true);
        }

        return newNode;
    },

    createFormFieldConstraintNode: function (nodeLabel, iconCls) {
        return {
            text: nodeLabel,
            type: 'layout',
            draggable: true,
            iconCls: iconCls,
            fbType: 'constraint',
            fbTypeContainer: 'constraints',
            leaf: false,
            expandable: false,
            expanded: true
        };
    },

    createContainerField: function (tree, container, containerValues, selectNode) {

        var label = containerValues === null ? container.label : containerValues.display_name,
            newNode = this.createContainerFieldNode(label, container.icon_class);

        newNode = tree.appendChild(newNode);

        if (containerValues !== null && containerValues.hasOwnProperty('name') && containerValues.name) {
            newNode.set('fbSensitiveFieldName', containerValues.name);
        }

        newNode.set(
            'object',
            new Formbuilder.extjs.components.formFieldContainer(
                this, newNode, container, this.availableFormFieldTemplates, containerValues
            )
        );

        tree.expand();

        if (selectNode === true) {
            this.tree.getSelectionModel().select(newNode, true);
        }

        return newNode;
    },

    createContainerFieldNode: function (nodeLabel, iconCls) {
        return {
            text: nodeLabel,
            type: 'layout',
            draggable: true,
            iconCls: iconCls,
            fbType: 'container',
            fbTypeContainer: 'fields', // container element should be treated as a normal field.
            leaf: true,
            expandable: true,
            expanded: true
        };
    },

    removeFormField: function (tree, record) {

        record.remove();

        if (this.id !== 0) {
            this.currentNode = null;
            // select root node "base"
            this.tree.getSelectionModel().select(this.tree.getRootNode(), true);
        }
    },

    cloneChild: function (tree, node) {

        var formFieldObject = node.data.object,
            formTypeValues = Ext.apply({}, formFieldObject.getData()),
            config = {},
            newNode = {},
            formElement = {};

        config.listeners = this.getTreeNodeListeners();

        if (node.data.fbType === 'field') {
            formTypeValues.name = formFieldObject.generateId();
            formElement = this.getFormTypeStructure(formFieldObject.getType());
            config = this.createFormFieldNode(formTypeValues.display_name, formFieldObject.getIconClass());
            newNode = node.createNode(config);
            newNode.set('object', new Formbuilder.extjs.components.formTypeBuilder(
                this, newNode, formElement, this.availableFormFieldTemplates, formTypeValues));
        } else if (node.data.fbType === 'constraint') {
            formElement = this.getFormTypeConstraintStructure(formFieldObject.getType());
            config = this.createFormFieldConstraintNode(formElement.label, formFieldObject.getIconClass());
            newNode = node.createNode(config);
            newNode.set('object', new Formbuilder.extjs.components.formFieldConstraint(
                this, newNode, formElement, formTypeValues));
        } else if (node.data.fbType === 'container') {
            formTypeValues.name = formFieldObject.generateId();
            formElement = this.getContainerTypeStructure(formFieldObject.getSubType());
            config = this.createContainerFieldNode(formElement.display_name, formFieldObject.getIconClass());
            newNode = node.createNode(config);
            newNode.set('object', new Formbuilder.extjs.components.formFieldContainer(
                this, newNode, formElement, this.availableFormFieldTemplates, formTypeValues));
        } else {
            Ext.MessageBox.alert(t('error'), 'invalid field type: ' + node.data.fbType);
        }

        if (node.hasOwnProperty('childNodes') && Ext.isArray(node.childNodes)) {
            Ext.Array.each(node.childNodes, function (childNode, i) {
                var clonedChildNode = this.cloneChild(tree, childNode);
                newNode.appendChild(clonedChildNode);
            }.bind(this));
        }

        return newNode;

    },

    getData: function (node) {

        var formFieldData = {};

        // initially undefined, use root to start:
        if (node === undefined) {
            node = this.tree.getRootNode();
        }

        node.set('cls', Ext.isString(node.get('cls')) ? node.get('cls').replace('tree_node_error', '') : '');

        if (typeof node.data.object === 'object') {
            formFieldData = node.data.object.getData();
        }

        if (formFieldData['fields']) {
            delete formFieldData['fields'];
        }

        if (formFieldData['constraints']) {
            delete formFieldData['constraints'];
        }

        if (formFieldData !== null && formFieldData.hasOwnProperty('name') && formFieldData.name) {
            node.set('fbSensitiveFieldName', formFieldData.name);
        }

        if (node.hasOwnProperty('childNodes') && Ext.isArray(node.childNodes)) {
            Ext.Array.each(node.childNodes, function (childNode, i) {
                var type = childNode.data.fbTypeContainer;
                if (!formFieldData[type]) {
                    formFieldData[type] = [];
                }
                formFieldData[type].push(this.getData(childNode));
            }.bind(this));
        }

        return formFieldData;
    },

    checkNodeHasUniqueName: function (node) {

        var c = 0,
            currentNodeName = node.fbTypeContainer + '.' + node.object.getName();

        Ext.each(this.getUsedFieldNames(this.tree.getRootNode(), []), function (name) {
            if (name === currentNodeName) {
                c++;
            }
        });

        if (c > 1) {
            node.object.getTreeNode().set('cls', 'tree_node_error');
            throw 'field name "' + currentNodeName + '" is already in use.';
        }
    },

    showImportPanel: function () {

        var importPanel;

        importPanel = new Formbuilder.extjs.components.formImporter(this);
        importPanel.showPanel();
    },

    importForm: function (formId) {
        this.formSelectionPanel.rebuildFormPanel(formId);
    },

    exportForm: function () {

        if (!this.formIsValid()) {
            return;
        }

        pimcore.helpers.download(Routing.generate('form_builder.controller.admin.export_form', {id: this.formId}));
    },

    exportFormEmailCsv: function () {
        var mailTypeField = this.exportPanel.query('combo'),
            mailTypeValue = 'all';

        if (mailTypeField.length === 1) {
            mailTypeValue = mailTypeField[0].getValue();
        }

        pimcore.helpers.download(
            Routing.generate('form_builder.controller.admin.csv_export.export', {id: this.formId, mailType: mailTypeValue})
        );
    },

    save: function (ev) {

        var formData, formConfig,
            formConditionalLogic, formFields;

        if (!this.formIsValid()) {
            return false;
        }

        formData = this.getData();

        formConfig = Ext.encode(this.formConfig);
        formConditionalLogic = Ext.encode(this.formConditionalsStructured);
        formFields = Ext.encode(formData);

        Ext.Ajax.request({
            url: '/admin/formbuilder/settings/save-form',
            method: 'post',
            params: {
                form_id: this.formId,
                form_config: formConfig,
                form_cl: formConditionalLogic,
                form_fields: formFields
            },
            success: this.saveOnComplete.bind(this),
            failure: this.saveOnError.bind(this)
        });

    },

    saveOnComplete: function (response) {

        var res = Ext.decode(response.responseText);

        if (res.success === false) {
            pimcore.helpers.showNotification(t('error'), res.message, 'error');
            return;
        }

        if (res.formId && res.formName) {
            this.parentPanel.setTitle(res.formName + ' (ID: ' + res.formId + ')');
        }

        this.formSelectionPanel.tree.getStore().load();
        pimcore.helpers.showNotification(t('success'), t('form_builder_builder_saved_successfully'), 'success');
    },

    /**
     * Generic error notification on form save event
     */
    saveOnError: function () {
        pimcore.helpers.showNotification(t('error'), t('form_builder_some_fields_cannot_be_saved'), 'error');
    },

    /**
     * Helper: find duplicate form type names
     */
    getUsedFieldNames: function (node, nodeNames) {

        if (node.data.object) {
            var fieldName = node.data.fbTypeContainer + '.' + node.data.object.getName();
            if (!in_array(node.data.fbType, ['constraint'])) {
                nodeNames.push(fieldName);
            }
        }

        if (node.hasOwnProperty('childNodes') && Ext.isArray(node.childNodes)) {
            Ext.Array.each(node.childNodes, function (childNode, i) {
                this.getUsedFieldNames(childNode, nodeNames);
            }.bind(this));
        }

        return nodeNames;
    },

    getFormTypeStructure: function (typeId) {
        var formTypeElement = false;
        if (this.availableFormFields.length === 0) {
            return formTypeElement;
        }

        Ext.Array.each(this.availableFormFields, function (formGroup, i) {
            Ext.Array.each(formGroup.fields, function (formGroupElement, groupI) {
                if (formGroupElement.type === typeId) {
                    formTypeElement = formGroupElement;
                    return false;
                }
            });
        });

        return Ext.clone(formTypeElement);
    },

    getFormTypeConstraintStructure: function (constraintId) {
        var formTypeConstraint = false;
        if (this.availableConstraints.length === 0) {
            return formTypeConstraint;
        }

        Ext.Array.each(this.availableConstraints, function (formConstraint) {
            if (formConstraint.id === constraintId) {
                formTypeConstraint = formConstraint;
                return false;
            }
        });

        return Ext.clone(formTypeConstraint);
    },

    getContainerTypeStructure: function (containerId) {
        var container = false;
        if (this.availableContainerTypes.length === 0) {
            return container;
        }

        Ext.Array.each(this.availableContainerTypes, function (containerType) {
            if (containerType.id === containerId) {
                container = containerType;
                return false;
            }
        });

        return Ext.clone(container);
    },

    hasFields: function (fbType) {
        return this.tree.getSelectionModel().getStore().findRecord('fbType', fbType) !== null;
    },

    getFields: function (fbTypes) {

        var treeStore = this.tree.getSelectionModel().getStore(),
            fields = [];

        Ext.Array.each(treeStore.queryBy(function (record) {

            var parentData;

            if (Ext.isArray(fbTypes) === true && in_array(record.get('fbType'), fbTypes) === false) {
                return false;
            } else if (Ext.isArray(fbTypes) === false && record.get('fbType') !== fbTypes) {
                return false;
            }

            if (record.getData().hasOwnProperty('object') === false) {
                return false;
            }

            if (record.hasOwnProperty('parentNode') === false) {
                return false;
            }

            parentData = record.parentNode.getData();

            if (parentData.hasOwnProperty('object') && parentData.object.getData().sub_type === 'fieldset') {
                return true;
            } else if (parentData.hasOwnProperty('fbType') && parentData.fbType === 'root') {
                return true;
            }

            return false;

        }).getRange(), function (field) {
            fields.push(field.getData().object.getData());
        });

        return fields;
    }
});
