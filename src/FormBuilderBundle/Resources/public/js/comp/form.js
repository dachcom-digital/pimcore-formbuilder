pimcore.registerNS('Formbuilder.comp.form');
Formbuilder.comp.form = Class.create({

    importIsRunning: false,
    availableFormFields: [],
    parentPanel: null,
    formId: null,
    formName: null,
    formMeta: {},
    formConfig: null,
    formConfigStore: {},
    formConditionalsStructured: {},
    formConditionalsStore: {},
    formFields: null,
    copyData: null,
    rootFields: [],
    allowedMoveElements: {
        'root': [
            'field'
        ],
        'field': [
            'constraint'
        ],
        'constraint': []
    },

    initialize: function (formData, parentPanel) {

        this.parentPanel = parentPanel;
        this.formId = formData.id;
        this.formName = formData.name;
        this.formMeta = formData.meta;
        this.formConfig = formData.config.length === 0 ? {} : formData.config;
        this.formConfigStore = formData.config_store;
        this.formConditionalsStructured = formData.conditional_logic;
        this.formConditionalsStore = formData.conditional_logic_store;
        this.formFields = formData.fields;
        this.availableFormFields = formData.fields_structure;
        this.availableConstraints = formData.validation_constraints;
        this.availableFormFieldTemplates = formData.fields_template;

        this.addLayout();
        this.initLayoutFields();

    },

    addLayout: function () {

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
            title: this.formName + ' (ID: ' + this.formId + ')',
            closable: true,
            cls: 'form-builder-form-panel',
            iconCls: 'form_builder_icon_root',
            autoScroll: true,
            autoEl: {
                'data-form-id': this.formId
            },
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
            border: false,
            layout: 'border',
            items: [this.tree, this.editPanel]

        });

        this.panel.on('beforedestroy', function () {

            if (this.formId && this.parentPanel.panels['form_' + this.formId]) {
                this.editPanel.removeAll();
                delete this.parentPanel.panels['form_' + this.formId];
            }

            if (this.parentPanel.tree.initialConfig !== null &&
                Object.keys(this.parentPanel.panels).length === 0) {
                this.parentPanel.tree.getSelectionModel().deselectAll();
            }

        }.bind(this));

        this.setCurrentNode('root');
        this.parentPanel.getEditPanel().add(this.panel);
        this.parentPanel.getEditPanel().setActiveTab(this.panel);
        pimcore.layout.refresh();

    },

    activate: function () {
        this.parentPanel.getEditPanel().setActiveTab(this.panel);
    },

    initLayoutFields: function () {

        if (!this.formFields) {
            return;
        }

        for (var i = 0; i < this.formFields.length; i++) {
            var node = this.recursiveAddNode(this.tree.getRootNode(), this.formFields[i], 'formType');
            if (node !== null) {
                this.tree.getRootNode().appendChild(node);
            }
        }

        this.tree.getRootNode().expand();
        // select root node "base"
        this.tree.getSelectionModel().select(this.tree.getRootNode(), true);

    },

    /**
     * @param scope
     * @param formTypeValues
     * @param type
     * @returns {*}
     */
    recursiveAddNode: function (scope, formTypeValues, type) {

        var newNode = null;

        if (type === 'formType') {

            var formGroupElement = this.getFormTypeStructure(formTypeValues.type);

            if (formGroupElement === false) {
                Ext.MessageBox.alert(t('error'), 'Form type structure for type "' + formTypeValues.type + '" not found.');
                return null;
            }

            newNode = this.createFormField(scope, formGroupElement, formTypeValues);

        } else if (type === 'constraint') {

            var constraintElement = this.getFormTypeConstraintStructure(formTypeValues.type);

            if (constraintElement === false) {
                Ext.MessageBox.alert(t('error'), 'Form type constraint structure for type "' + formTypeValues.type + '" not found.');
                return null;
            }

            newNode = this.createFormFieldConstraint(scope, constraintElement, formTypeValues);
        }

        if (formTypeValues.constraints) {
            for (var i = 0; i < formTypeValues.constraints.length; i++) {
                this.recursiveAddNode(newNode, formTypeValues.constraints[i], 'constraint');
            }
        }

        if (formTypeValues.fields) {
            for (var i2 = 0; i2 < formTypeValues.fields.length; i2++) {
                this.recursiveAddNode(newNode, formTypeValues.fields[i2], 'formType');
            }
        }

        return newNode;
    },

    /**
     *
     * @returns {{beforeselect, select, itemcontextmenu, beforeitemmove}}
     */
    getTreeNodeListeners: function () {

        return {
            'beforeselect': this.onTreeNodeBeforeSelect.bind(this),
            'select': this.onTreeNodeSelect.bind(this),
            'itemcontextmenu': this.onTreeNodeContextMenu.bind(this),
            'beforeitemmove': this.onTreeNodeBeforeMove.bind(this)
        };

    },

    /**
     * @param node
     * @param oldParent
     * @param newParent
     * @param index
     * @param eOpts
     */
    onTreeNodeBeforeMove: function (node, oldParent, newParent, index, eOpts) {

        var targetType = newParent.data.fbType,
            elementType = node.data.fbType;

        return Ext.Array.contains(this.allowedMoveElements[targetType], elementType);

    },

    /**
     * @param tree
     * @returns {boolean}
     */
    onTreeNodeBeforeSelect: function (tree) {
        try {
            this.checkCurrentNodeValidation();
        } catch (e) {
            Ext.MessageBox.alert(t('error'), e);
            return false;
        }
    },

    /**
     * @param tree
     * @param record
     * @param item
     * @param index
     * @param e
     * @param eOpts
     */
    onTreeNodeSelect: function (tree, record, item, index, e, eOpts) {

        var fbObject;

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
        }

        this.editPanel.updateLayout();

    },

    /**
     * @param tree
     * @param record
     * @param item
     * @param index
     * @param ev
     */
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

        try {
            this.checkCurrentNodeValidation();
        } catch (e) {
            return;
        }

        this.tree.getSelectionModel().select(record, true);

        deleteAllowed = parentType !== 'root';
        if (record.data.object && record.data.object.storeData.locked) {
            deleteAllowed = false;
        }

        //add form items
        if (parentType === 'root' && _.availableFormFields.length > 0) {

            for (var i = 0; i < _.availableFormFields.length; i++) {

                var formGroup = _.availableFormFields[i],
                    formGroupElements = [];

                if (formGroup.fields.length === 0) {
                    continue;
                }

                for (var groupI = 0; groupI < formGroup.fields.length; groupI++) {

                    var formGroupElement = formGroup.fields[groupI];

                    formGroupElements.push({
                        text: formGroupElement.label,
                        iconCls: formGroupElement.icon_class,
                        handler: this.createFormField.bind(_, record, formGroupElement, null, true)
                    });
                }

                layoutElem.push(new Ext.menu.Item({
                    text: formGroup.label,
                    iconCls: formGroup.icon_class,
                    hideOnClick: false,
                    menu: formGroupElements
                }));

            }

            menu.add(new Ext.menu.Item({
                text: t('form_builder_add_form_item'),
                iconCls: 'form_builder_icon_item_add',
                hideOnClick: false,
                menu: layoutElem
            }));
        }

        //delete menu
        if (parentType !== 'root') {
            menu.add(new Ext.menu.Item({
                text: t('copy'),
                iconCls: 'pimcore_icon_copy',
                hideOnClick: true,
                handler: this.copyFormField.bind(this, tree, record)
            }));
        }

        //constraint menu
        if (parentType === 'field' && record.data.object.allowedConstraints.length > 0) {

            var constraintElements = [];
            Ext.each(record.data.object.allowedConstraints, function (constraintId) {
                var constraint = _.getFormTypeConstraintStructure(constraintId);
                constraintElements.push(new Ext.menu.Item({
                    text: constraint.label,
                    iconCls: constraint.icon_class,
                    hideOnClick: true,
                    handler: _.createFormFieldConstraint.bind(_, record, constraint, null)
                }));
            });

            menu.add(new Ext.menu.Item({
                text: t('form_builder_add_validation'),
                iconCls: 'form_builder_icon_validation_add',
                hideOnClick: false,
                menu: constraintElements
            }));
        }

        if (this.copyData !== null) {

            var copyType = this.copyData.data.type;

            if (parentType === 'root') {
                if (copyType !== 'validator') {
                    showPaste = true;
                }
            } else {
                //@todo: check additional types.
                //showPaste = true;
            }
        }

        if (showPaste === true) {
            menu.add(new Ext.menu.Item({
                text: t('paste'),
                iconCls: 'pimcore_icon_paste',
                handler: this.pasteFormField.bind(this, tree, record)
            }));
        }

        if (deleteAllowed) {
            menu.add(new Ext.menu.Item({
                text: t('delete'),
                iconCls: 'pimcore_icon_delete',
                handler: this.removeFormField.bind(this, tree, record)
            }));
        }

        menu.showAt(ev.pageX, ev.pageY);
    },

    setCurrentNode: function (cn) {
        this.currentNode = cn;
    },

    checkCurrentNodeValidation: function () {

        if (!this.currentNode) {
            return;
        }

        if (this.currentNode === 'root') {
            this.rootNodeIsValid();
        } else {
            this.fieldNodeIsValid();
        }
    },

    /**
     * 1. check field name uniqueness
     * 2. check field validation of fb-field object
     *
     * throws exception
     */
    fieldNodeIsValid: function () {

        var c = 0, currentNodeName;

        this.dispatchFieldNodeValueParser();

        currentNodeName = this.currentNode.fbType + '.' + this.currentNode.object.getName();

        Ext.each(this.getUsedFieldNames(this.tree.getRootNode(), []), function (name) {
            if (name === currentNodeName) {
                c++;
            }
        });

        this.currentNode.object.getTreeNode().set('cls', '');

        if (c > 1) {
            this.currentNode.object.getTreeNode().set('cls', 'tree_node_error');
            throw 'field name "' + currentNodeName + '" is already in use.';
        }

        if (!this.currentNode.object.isValid()) {
            this.currentNode.object.getTreeNode().set('cls', 'tree_node_error');
            throw t('form_builder_form_type_invalid')
        }
    },

    dispatchFieldNodeValueParser: function () {
        this.currentNode.object.applyData();
    },

    /**
     * throws exception
     */
    rootNodeIsValid: function () {
        if (this.rootPanel === undefined || this.importIsRunning === true) {
            //root panel not initialized yet.
            return;
        }

        this.dispatchRootNodeValueParser();

        this.tree.getRootNode().set('cls', '');

        if (this.rootFields.length > 0) {
            this.rootFields.each(function (field) {
                if (typeof field.getValue === 'function') {
                    if (field.allowBlank !== true && field.getValue() === '') {
                        this.tree.getRootNode().set('cls', 'tree_node_error');
                        throw field.getName() + ' cannot be empty.';
                    }
                }
            }.bind(this));
        }

        if (this.formName.length <= 2 || in_array(this.formName.toLowerCase(), this.parentPanel.getConfig().forbidden_form_field_names)) {
            this.tree.getRootNode().set('cls', 'tree_node_error');
            throw this.formName.toLowerCase() + ' is a reserved name and cannot be used.';
        }
    },

    dispatchRootNodeValueParser: function () {

        var formConditionals = {},
            formAttributes = {},
            parsedFormAttributes = {};

        // save root node data
        this.rootFields = this.rootPanel.getForm().getFields();

        var items = this.rootPanel.queryBy(function () {
            return true;
        });

        for (var i = 0; i < items.length; i++) {

            if (typeof items[i].getValue === 'function') {

                var val = items[i].getValue(),
                    fieldName = items[i].name;

                if (fieldName) {
                    if (fieldName.substring(0, 3) === 'cl.') {
                        formConditionals[fieldName] = val;
                    } else if (fieldName.substring(0, 11) === 'attributes.') {
                        formAttributes[fieldName] = val;
                    } else {
                        this.formConfig[fieldName] = val;
                    }
                }
            }
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
     * @returns Ext.form.FormPanel
     */
    getRootPanel: function () {

        var methodStore = new Ext.data.ArrayStore({
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
            keyValueRepeater = new Formbuilder.comp.types.keyValueRepeater(
                {
                    'label': t('form_builder_form_attribute_name') + ' & ' + t('form_builder_form_attribute_value'),
                    'id': 'attributes'
                },
                this.formConfig['attributes'] ? this.formConfig['attributes'] : [],
                this.formConfigStore.attributes,
                false
            ),
            clBuilder = new Formbuilder.comp.conditionalLogic.builder(this.formConditionalsStructured, this.formConditionalsStore, this);

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
            items: [
                {
                    xtype: 'combo',
                    fieldLabel: t('form_builder_email_csv_export_mail_type'),
                    queryDelay: 0,
                    displayField: 'key',
                    valueField: 'value',
                    mode: 'local',
                    labelAlign: 'top',
                    store: new Ext.data.ArrayStore({
                        fields: ['value', 'key'],
                        data: [
                            ['all', t('form_builder_email_csv_export_mail_type_all')],
                            ['only_main', t('form_builder_email_csv_export_mail_type_only_main')],
                            ['only_copy', t('form_builder_email_csv_export_mail_type_only_copy')],
                        ]
                    }),
                    value: 'all',
                    editable: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    summaryDisplay: true,
                    allowBlank: false,
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
                this.exportPanel

            ]
        });

        return this.rootPanel;
    },

    /**
     * @returns Ext.Toolbar
     */
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

    /**
     * Display info window with current form meta information
     */
    showFormMetaInfo: function () {
        new Formbuilder.comp.extensions.formMetaData(this.formId, this.formMeta);
    },

    /**
     * @param tree
     * @param formType
     * @param formTypeValues
     * @param selectNode
     * @returns {*|{text: *, type: string, draggable: boolean, iconCls: (null|*|string), fbType: string, fbTypeContainer: string, leaf: boolean, expandable: boolean, expanded: boolean}}
     */
    createFormField: function (tree, formType, formTypeValues, selectNode) {

        var newNode = this.createFormFieldNode(formType, formTypeValues),
            formObject;

        newNode = tree.appendChild(newNode);

        formObject = new Formbuilder.comp.type.formTypeBuilder(this, newNode, formType, this.availableFormFieldTemplates, formTypeValues);
        newNode.set('object', formObject);

        tree.expand();

        if (selectNode === true) {
            this.tree.getSelectionModel().select(newNode, true);
        }

        return newNode;
    },

    /**
     * @param formType
     * @param formTypeValues
     * @returns {{text: *, type: string, draggable: boolean, iconCls: (null|*|string), fbType: string, fbTypeContainer: string, leaf: boolean, expandable: boolean, expanded: boolean}}
     */
    createFormFieldNode: function (formType, formTypeValues) {
        return {
            text: formTypeValues ? formTypeValues.display_name : formType.label,
            type: 'layout',
            draggable: true,
            iconCls: formType.icon_class,
            fbType: 'field',
            fbTypeContainer: 'fields',
            leaf: false,
            expandable: false,
            expanded: true
        };
    },

    /**
     * @param tree
     * @param constraint
     * @param constraintValues
     * @returns {*|{text, type: string, draggable: boolean, iconCls: (null|*|string), fbType: string, fbTypeContainer: string, leaf: boolean, expandable: boolean, expanded: boolean}}
     */
    createFormFieldConstraint: function (tree, constraint, constraintValues) {

        var newNode = this.createFormFieldConstraintNode(constraint);

        newNode = tree.appendChild(newNode);
        newNode.set('object', new Formbuilder.comp.type.formFieldConstraint(this, newNode, constraint, constraintValues));

        tree.expand();

        return newNode;
    },

    /**
     * @param constraintType
     * @returns {{text, type: string, draggable: boolean, iconCls: (null|*|string), fbType: string, fbTypeContainer: string, leaf: boolean, expandable: boolean, expanded: boolean}}
     */
    createFormFieldConstraintNode: function (constraintType) {
        return {
            text: constraintType.label,
            type: 'layout',
            draggable: true,
            iconCls: constraintType.icon_class,
            fbType: 'constraint',
            fbTypeContainer: 'constraints',
            leaf: false,
            expandable: false,
            expanded: true
        };
    },

    /**
     * @param tree
     * @param record
     */
    copyFormField: function (tree, record) {
        this.copyData = this.cloneChild(tree, record, true);
    },

    /**
     * @param tree
     * @param record
     */
    pasteFormField: function (tree, record) {

        var node = this.copyData,
            newNode = this.cloneChild(tree, node, false);

        record.appendChild(newNode);
        tree.updateLayout();

    },

    /**
     * @param tree
     * @param record
     */
    removeFormField: function (tree, record) {

        record.remove();

        if (this.id !== 0) {
            this.currentNode = null;
            // select root node "base"
            this.tree.getSelectionModel().select(this.tree.getRootNode(), true);
        }
    },

    /**
     * @param tree
     * @param node
     * @param isCopy
     * @returns {{}}
     */
    cloneChild: function (tree, node, isCopy) {

        var formFieldObject = node.data.object,
            formTypeValues = Ext.apply({}, formFieldObject.getData()),
            config = {},
            newNode = {},
            nodeType = {
                'icon_class': formFieldObject.iconClass
            };

        config.listeners = this.getTreeNodeListeners();

        if (node.data.fbType === 'field') {

            //reset name
            formTypeValues.name = Ext.id(null, 'field_');

            nodeType.type = formTypeValues.type;
            nodeType.label = formTypeValues.display_name;
            nodeType.configuration_layout = formFieldObject.configurationLayout;
            config = this.createFormFieldNode(nodeType, formTypeValues);
            newNode = node.createNode(config);
            newNode.set('object', new Formbuilder.comp.type.formTypeBuilder(
                this, newNode, nodeType, this.availableFormFieldTemplates, formTypeValues));

        } else if (node.data.fbType === 'constraint') {
            nodeType.label = formFieldObject.typeName;
            config = this.createFormFieldConstraintNode(nodeType, formTypeValues);
            newNode = node.createNode(config);
            newNode.set('object', new Formbuilder.comp.type.formFieldConstraint(
                this, newNode, nodeType, formTypeValues));
        } else {
            Ext.MessageBox.alert(t('error'), 'invalid field type: ' + node.data.fbType);
        }

        var len = node.childNodes ? node.childNodes.length : 0;

        // Move child nodes across to the copy if required
        for (var i = 0; i < len; i++) {
            var childNode = node.childNodes[i];
            var clonedChildNode = this.cloneChild(tree, childNode, isCopy);
            newNode.appendChild(clonedChildNode);
        }

        return newNode;

    },

    /**
     * @returns {*|{}}
     */
    getData: function () {
        return this.getNodeData(this.tree.getRootNode());
    },

    /**
     * @param node
     * @returns {{}}
     */
    getNodeData: function (node) {

        var formFieldData = {};

        if (typeof node.data.object === 'object') {
            formFieldData = node.data.object.getData();
            node.set('cls', '');
            if (!node.data.object.isValid()) {
                node.set('cls', 'tree_node_error');
                throw t('form_builder_form_type_invalid')
            }
        }

        if (formFieldData['fields']) {
            delete formFieldData['fields'];
        }

        if (formFieldData['constraints']) {
            delete formFieldData['constraints'];
        }

        if (node.childNodes.length > 0) {
            for (var i = 0; i < node.childNodes.length; i++) {
                var type = node.childNodes[i].data.fbTypeContainer;
                if (!formFieldData[type]) {
                    formFieldData[type] = [];
                }
                formFieldData[type].push(this.getNodeData(node.childNodes[i]));
            }
        }

        return formFieldData;
    },

    /**
     * Create Import Panel (Upload File)
     */
    showImportPanel: function () {
        var importPanel = new Formbuilder.comp.importer(this);
        importPanel.showPanel();
    },

    /**
     * @param importedFormData
     */
    importForm: function (importedFormData) {

        this.importIsRunning = true;

        this.parentPanel.getEditPanel().removeAll();

        this.formConfig = importedFormData.data.config;
        this.formFields = importedFormData.data.fields;
        if (importedFormData.data.hasOwnProperty('conditional_logic')) {
            this.formConditionalsStructured = importedFormData.data.conditional_logic;
        }

        this.addLayout();
        this.initLayoutFields();

        this.importIsRunning = false;

    },

    /**
     * Trigger browser download (if form is valid)
     * -> for form export
     */
    exportForm: function () {

        try {
            this.checkCurrentNodeValidation();
        } catch (e) {
            Ext.MessageBox.alert(t('error'), e);
            return;
        }

        pimcore.helpers.download('/admin/formbuilder/settings/get-export-file/' + this.formId);
    },

    /**
     * Trigger browser download
     * -> for csv export of sent emails
     */
    exportFormEmailCsv: function () {
        var mailTypeField = this.exportPanel.query('combo'),
            mailTypeValue = 'all';

        if (mailTypeField.length === 1) {
            mailTypeValue = mailTypeField[0].getValue();
        }

        pimcore.helpers.download('/admin/formbuilder/export/mail-csv-export/' + this.formId + '?mailType=' + mailTypeValue);
    },

    /**
     * @param ev
     * @returns {boolean}
     */
    save: function (ev) {

        var formData = {}, formConfig,
            formConditionalLogic, formFields;

        try {
            this.checkCurrentNodeValidation();
        } catch (e) {
            Ext.MessageBox.alert(t('error'), e);
            return false;
        }

        try {
            formData = this.getData();
        } catch (e) {
            Ext.MessageBox.alert(t('error'), e);
            return false;
        }

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

    /**
     * @param response
     */
    saveOnComplete: function (response) {

        var res = Ext.decode(response.responseText);

        if (res.success === false) {
            pimcore.helpers.showNotification(t('error'), res.message, 'error');
            return;
        }

        if (res.formId && res.formName) {
            this.panel.setTitle(res.formName + ' (ID: ' + res.formId + ')');
        }

        this.parentPanel.tree.getStore().load();
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
     *
     * @param node
     * @param nodeNames
     * @returns {Array}
     */
    getUsedFieldNames: function (node, nodeNames) {

        if (node.data.object) {

            var fieldName = node.data.fbType + '.' + node.data.object.getName();
            if (node.data.fbType !== 'constraint') {
                nodeNames.push(fieldName);
            }
        }

        if (node.childNodes.length > 0) {
            for (var i = 0; i < node.childNodes.length; i++) {
                this.getUsedFieldNames(node.childNodes[i], nodeNames);
            }
        }

        return nodeNames;
    },

    /**
     * @param typeId
     * @returns {boolean}
     */
    getFormTypeStructure: function (typeId) {

        var formTypeElement = false;
        if (this.availableFormFields.length === 0) {
            return formTypeElement;
        }

        for (var i = 0; i < this.availableFormFields.length; i++) {
            var formGroup = this.availableFormFields[i];
            for (var groupI = 0; groupI < formGroup.fields.length; groupI++) {
                var formGroupElement = formGroup.fields[groupI];
                if (formGroupElement.type === typeId) {
                    formTypeElement = formGroupElement;
                    break;
                }
            }
        }

        return formTypeElement;
    },

    /**
     * @param constraintId
     * @returns {boolean}
     */
    getFormTypeConstraintStructure: function (constraintId) {

        var formTypeConstraint = false;
        if (this.availableConstraints.length === 0) {
            return formTypeConstraint;
        }

        for (var i = 0; i < this.availableConstraints.length; i++) {
            var formConstraint = this.availableConstraints[i];
            if (formConstraint.id === constraintId) {
                formTypeConstraint = formConstraint;
                break;
            }
        }

        return formTypeConstraint;
    }
});