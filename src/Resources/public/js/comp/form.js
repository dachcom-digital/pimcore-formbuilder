pimcore.registerNS('Formbuilder.comp.form');
Formbuilder.comp.form = Class.create({

    availableFormFields: [],

    parentPanel: null,

    formId: null,

    formName: null,

    formDate: null,

    formConfig: null,

    formFields: null,

    formIsValid: false,

    copyData: null,

    rootFields: [],

    allowedMoveElements: {
        'root' : [
            'container',
            'displayGroup',
            'element'
        ],
        'container' : [
            'displayGroup',
            'container',
            'element'
        ],
        'displayGroup' : [
            'element'
        ],
        'element' : [
            'validator'
        ],
        'validator' : [
            'element'
        ]
    },

    initialize: function(formData, parentPanel) {

        this.parentPanel = parentPanel;

        this.formId = formData.id;

        this.formName = formData.name;

        this.formDate = formData.date;

        this.formConfig = formData.config.length === 0 ? {} : formData.config;

        this.formFields = formData.fields;

        this.availableFormFields = formData.fields_structure

        this.availableFormFieldTemplates = formData.fields_template

        this.addLayout();
        this.initLayoutFields();

    },

    addLayout: function() {

        this.tree = Ext.create('Ext.tree.Panel', {

            region: 'west',
            autoScroll: true,
            listeners: this.getTreeNodeListeners(),
            animate:false,
            split: true,
            enableDD: true,
            width: 300,

            root: {
                id: '0',
                text: t('form_builder_base'),
                iconCls:'form_builder_icon_root',
                fbType: 'root',
                isTarget: true,
                leaf:true,
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
            autoScroll: true
        });

        this.panel = new Ext.Panel({
            title: this.formName + ' (ID: ' + this.formId + ')',
            id: this.formId,
            closable: true,
            iconCls: 'form_builder_icon_root',
            autoScroll: true,
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

        this.panel.on('beforedestroy', function() {

            if( this.formId && this.parentPanel.panels['form_' + this.formId] ) {
                this.editPanel.removeAll();
                delete this.parentPanel.panels['form_' + this.formId];
            }

            if( this.parentPanel.tree.initialConfig !== null &&
                Object.keys( this.parentPanel.panels ).length === 0 ) {
                this.parentPanel.tree.getSelectionModel().deselectAll();
            }

        }.bind(this));

        this.parentPanel.getEditPanel().add(this.panel);
        this.editPanel.add(this.getRootPanel());

        this.setCurrentNode('root');
        this.parentPanel.getEditPanel().setActiveTab(this.panel);

        pimcore.layout.refresh();

    },

    activate: function() {
        this.parentPanel.getEditPanel().setActiveTab(this.panel);
    },

    initLayoutFields: function() {

        if (!this.formFields) {
            return;
        }

        for (var i = 0; i < this.formFields.length; i++) {
            var node = this.recursiveAddNode(this.formFields[i], this.tree.getRootNode());
            if(node !== null) {
                this.tree.getRootNode().appendChild(node);
            }
        }

        this.tree.getRootNode().expand();

    },

    recursiveAddNode: function(formTypeValues, scope) {

        var stype = null, fn = null, newNode = null;

        if( formTypeValues.isValidator === true) {
            stype = 'validator';
        }

        var formGroupElement = this.getFormTypeStructure(formTypeValues.type);

        if(formGroupElement === false) {
            Ext.MessageBox.alert(t('error'), 'Form type structure for type "' + formTypeValues.type + '" not found.');
            return null;
        }

        fn = this.createFormField.bind(this, scope, formGroupElement, formTypeValues);
        newNode = fn();

        if (formTypeValues.fields) {
            for (var i = 0; i < formTypeValues.fields.length; i++) {
                this.recursiveAddNode(formTypeValues.fields[i], newNode);
            }
        }

        return newNode;
    },

    getTreeNodeListeners: function() {

        return {
            'beforeselect'      : this.onTreeNodeBeforeSelect.bind(this),
            'select'            : this.onTreeNodeSelect.bind(this),
            'itemcontextmenu'   : this.onTreeNodeContextMenu.bind(this),
            'beforeitemmove'    : this.onTreeNodeBeforeMove.bind(this),
        };

    },

    onTreeNodeBeforeMove: function(node, oldParent, newParent, index, eOpts){

        var targetType = newParent.data.fbType,
            elementType = node.data.fbType;

        return Ext.Array.contains(this.allowedMoveElements[ targetType ], elementType  );

    },

    onTreeNodeBeforeSelect: function(tree) {
        try {
            this.saveCurrentNode();
        } catch (e) {
            Ext.MessageBox.alert(t('error'), e);
            return false;
        }
    },

    onTreeNodeSelect: function(tree, record, item, index, e, eOpts) {

        this.editPanel.removeAll();

        if (record.data.object) {

            if (record.data.object.storeData.locked) {
                return;
            }

            this.editPanel.add(record.data.object.renderLayout());
            this.setCurrentNode(record.data.object);
        }

        if (record.data.root) {
            this.editPanel.add(this.getRootPanel());
            this.setCurrentNode('root');
        }

        this.editPanel.updateLayout();

    },

    onTreeNodeContextMenu: function(tree, record, item, index, e, eOpts) {

        var _ = this,
            parentType = 'root',
            deleteAllowed,
            showPaste = false,
            menu = new Ext.menu.Menu();

        e.stopEvent();
        tree.select();

        if (record.data.object) {
            parentType = record.data.object.type;
        }

        deleteAllowed = parentType !== 'root';

        if (record.data.object && record.data.object.storeData.locked) {
            deleteAllowed = false;
        }

        var layoutElem = [],
            layouts = Object.keys(Formbuilder.comp.type);

        //add form items
        if (parentType === 'root' && _.availableFormFields.length > 0) {

            for (var i = 0; i < _.availableFormFields.length; i++) {

                var formGroup = _.availableFormFields[i],
                    formGroupElements = [];

                if(formGroup.fields.length === 0) {
                    continue;
                }

                for (var groupI = 0; groupI < formGroup.fields.length; groupI++) {

                    var formGroupElement = formGroup.fields[groupI];

                    formGroupElements.push({
                        text: formGroupElement.label,
                        iconCls: formGroupElement.icon_class,
                        handler: this.createFormField.bind(_, record, formGroupElement, null)
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

        if (parentType !== 'root') {

            menu.add(new Ext.menu.Item({
                text: t('copy'),
                iconCls: 'pimcore_icon_copy',
                hideOnClick: true,
                handler: this.copyFormField.bind(this, tree, record)
            }));
        }

        if (this.copyData !== null) {

            var copyType = this.copyData.data.type;

            if(parentType === 'root') {

                if(copyType === 'displayGroup' || copyType === 'container') {
                    showPaste = true;
                } else if(in_array(copyType, allowedTypes[parentType])){
                    showPaste = true;
                }

            } else {

                if( !record.data.object.storeData.isValidator) {

                    if(copyType === 'container' && parentType !== 'displayGroup') {
                        showPaste = true;
                    } else if(copyType === 'displayGroup' && parentType === 'container') {
                        showPaste = true;
                    } else if(in_array(copyType, allowedTypes[parentType])){
                        showPaste = true;
                    } else if(in_array(copyType, allowedValidators[parentType])){
                        showPaste = true;
                    }
                }
            }
        }

        if(showPaste === true) {

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

        menu.showAt(e.pageX, e.pageY);
    },

    setCurrentNode: function(cn) {
        this.currentNode = cn;
    },

    saveCurrentNode: function() {

        if (!this.currentNode) {
            return;
        }

        if (this.currentNode === 'root') {
            this.saveRootNode();
        } else {
            this.saveSubNodes();
        }
    },

    saveRootNode: function() {

        // save root node data
        var items = this.rootPanel.queryBy(function() {
            return true;
        });

        var attrCouples = {};
        for (var i = 0; i < items.length; i++) {
            if (typeof items[i].getValue === 'function') {

                var val = items[i].getValue(),
                    fieldName = items[i].name;

                if (fieldName.substring(0, 7) == 'attrib_') {

                    if( val !== '') {

                        var elements = fieldName.split('_');

                        if( !attrCouples[elements[2]] ) {
                            attrCouples[elements[2]] = {'name' : null, 'value' : null}
                        }

                        attrCouples[ elements[2] ][ elements[1] ] = val;

                    }

                } else if (fieldName === 'name') {
                    this.formName = val;
                } else if (fieldName === 'date') {
                    this.formDate = val;
                } else {
                    this.formConfig[fieldName] = val;
                }
            }
        }

        this.formConfig['attributes'] = [];
        if( Object.keys(attrCouples).length > 0) {
            Ext.Object.each(attrCouples, function(name, value) {
                this.formConfig['attributes'].push( value );
            }.bind(this));
        }

        this.formIsValid = this.rootFormIsValid();

    },

    saveSubNodes: function() {

        this.currentNode.applyData();

        var c = 0, n = this.currentNode.getData().name;
        Ext.each(this.getUsedFieldNames(this.tree.getRootNode(), []), function(name) {
            if(name === n) { c++; }
        });

        if(c > 1) {
            throw 'field name is already in use.';
        }

    },

    getRootPanel: function() {

        var methodStore = new Ext.data.ArrayStore({
            fields: ['value','label'],
            data : [['post','POST'],['get','GET']]
        });

        var attributeStore = new Ext.data.ArrayStore({
            fields: ['value','label'],
            data : [
                ['class','class'],
                ['id','id'],
                ['title','title'],
                ['onclick','onclick'],
                ['ondbclick','ondbclick'],
                ['onkeydown','onkeydown'],
                ['onkeypress','onkeypress'],
                ['onkeyup','onkeyup'],
                ['onmousedown','onmousedown'],
                ['onmousemove','onmousemove'],
                ['onmouseout','onmouseout'],
                ['onmouseover','onmouseover'],
                ['onmouseup','onmouseup'],
                ['onselect','onselect'],
                ['onreset','onreset'],
                ['onsubmit','onsubmit']
            ]
        });

        var encStore = new Ext.data.ArrayStore({
            fields: ['value','label'],
            data: [
                ['text/plain','text/plain'],
                ['application/x-www-form-urlencoded','application/x-www-form-urlencoded'],
                ['multipart/form-data','multipart/form-data']
            ]
        });

        // meta-data
        var addMetaData = function(name, value) {

            if(typeof name !== 'string') {
                name = '';
            }

            if(typeof value !== 'string') {
                value = '';
            }

            var count = this.metaDataPanel.query('button').length+1;

            var combolisteners = {
                'afterrender': function(el) {
                    el.getEl().parent().applyStyles({
                        float: 'left',
                        'margin-right': '5px'
                    });
                }
            };

            var compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: 'padding-bottom:5px;',
                items: [
                    {
                        xtype: 'combo',
                        name: 'attrib_name_' + count,
                        fieldLabel: t('form_builder_attribute_name'),
                        queryDelay: 0,
                        displayField: 'label',
                        valueField: 'value',
                        mode: 'local',
                        store: attributeStore,
                        editable: true,
                        triggerAction: 'all',
                        anchor: '100%',
                        value: name,
                        summaryDisplay: true,
                        allowBlank: false,
                        flex: 1,
                        listeners: combolisteners
                    },
                    {
                        xtype: 'textfield',
                        name: 'attrib_value_' + count,
                        fieldLabel: t('form_builder_attribute_value'),
                        anchor: '100%',
                        summaryDisplay: true,
                        allowBlank: false,
                        value: value,
                        flex: 1,
                        listeners: combolisteners
                    }
                ]
            });

            compositeField.add([{
                xtype: 'button',
                iconCls: 'pimcore_icon_delete',
                style: 'float:left;',
                handler: function(compositeField, el) {
                    this.metaDataPanel.remove(compositeField);
                    this.metaDataPanel.updateLayout();
                }.bind(this, compositeField)
            },{
                xtype: 'box',
                style: 'clear:both;'
            }]);

            this.metaDataPanel.add(compositeField);
            this.metaDataPanel.updateLayout();

        }.bind(this);

        this.metaDataPanel = new Ext.form.FieldSet({

            title:  t('form_builder_form_attribute_name') + ' & ' + t('form_builder_form_attribute_value'),
            collapsible: false,
            autoHeight:true,
            width: '100%',
            style: 'margin-top: 20px;',
            items: [{
                xtype: 'toolbar',
                style: 'margin-bottom: 10px;',
                items: ['->', {
                    xtype: 'button',
                    text: t('add'),
                    iconCls: 'pimcore_icon_add',
                    handler: addMetaData,
                    tooltip: {
                        title:'',
                        text: t('form_builder_add_metadata')
                    }
                }]
            }]
        });

        try {
            if(typeof this.formConfig.attributes == 'object' && this.formConfig.attributes.length > 0) {
                this.formConfig.attributes.forEach(function(field) {
                    addMetaData(field['name'], field['value'] );
                });
            }

        } catch (e) {}

        this.rootPanel = new Ext.form.FormPanel({

            title: t('form_builder_form_configuration'),
            bodyStyle: 'padding:10px',
            border: false,
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
                    displayField:'label',
                    valueField: 'value',
                    mode: 'local',
                    store: methodStore,
                    editable: true,
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
                    displayField:'label',
                    valueField: 'value',
                    mode: 'local',
                    store: encStore,
                    editable: true,
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
                    checked: this.formConfig.useAjax === undefined ? true : false,
                    value: this.formConfig.useAjax
                },

                this.metaDataPanel

            ]
        });

        this.rootFields = this.rootPanel.getForm().getFields();

        return this.rootPanel;
    },

    createFormField: function(tree, formType, formTypeValues) {

        var newNode = {
            text: formTypeValues ? formTypeValues.display_name : formType.label,
            type: 'layout',
            draggable: true,
            iconCls: formType.icon_class,
            fbType: formType === 'container' ? 'container' : ( formType === 'displayGroup' ? 'displayGroup' : 'element' ),
            leaf: false,
            expandable: formType === 'container' || formType === 'displayGroup',
            expanded: true
        };

        newNode = tree.appendChild(newNode);
        newNode.set('object', new Formbuilder.comp.type.formTypeBuilder(this, newNode, formType, this.availableFormFieldTemplates, formTypeValues));

        tree.expand();

        return newNode;
    },

    createFormFieldConstraint: function(type) {

        var newNode = {
            type: 'layout',
            draggable: true,
            iconCls: 'form_builder_icon_validator',
            fbType: 'validator',
            text: nodeLabel,
            leaf: false,
            expandable: false,
            expanded: true
        };

        newNode = this.appendChild(newNode);
        newNode.set('object', new Formbuilder.comp.validator[type](newNode, initData, this) );

    },

    copyFormField: function(tree, record) {

        this.copyData = {};

        var newNode = this.cloneChild(tree, record);
        this.copyData = newNode;

    },

    pasteFormField: function(tree, record) {

        var node = this.copyData;
        var newNode = this.cloneChild(tree, node);

        record.appendChild(newNode);
        tree.updateLayout();

    },

    removeFormField: function(tree, record) {

        if (this.id !== 0) {
            if (this.currentNode === record.data.object) {
                this.currentNode = null;
                var f = this.onTreeNodeSelect.bind(this, this.tree, this.tree.getRootNode());
                f();
            }

            record.remove();
        }
    },

    cloneChild: function(tree, node) {

        var theReference = this,
            nodeLabel = node.data.text,
            nodeType = node.data.object.type,
            config = {
                text: nodeLabel,
                type: nodeType,
                leaf: false,
                expandable: nodeType === 'container' || nodeType === 'displayGroup',
                expanded: true
            };

        config.listeners = theReference.getTreeNodeListeners();

        if (node.data.object) {
            config.iconCls = node.data.object.getIconClass();
        }

        var newNode = node.createNode(config),
            theData = {};

        if (node.data.object) {
            theData = Ext.apply(theData, node.data.object.storeData);
        }

        var newObjectClass = null;

        if( node.data.object.storeData.isValidator === true) {
            newObjectClass = Formbuilder.comp.validator[nodeType];
        } else {
            newObjectClass = Formbuilder.comp.type[nodeType];
        }

        newNode.data.object = new newObjectClass(newNode, theData);

        var len = node.childNodes ? node.childNodes.length : 0;

        // Move child nodes across to the copy if required
        for (var i = 0; i < len; i++) {
            var childNode = node.childNodes[i];
            var clonedChildNode = this.cloneChild(tree, childNode);
            newNode.appendChild(clonedChildNode);
        }

        return newNode;

    },

    getData: function() {
        return this.getNodeData(this.tree.getRootNode());
    },

    getNodeData: function(node) {

        var formFieldData = {};

        if(typeof node.data.object === 'object') {

            formFieldData = node.data.object.getData();

            var fieldName = formFieldData.name,
                view = this.tree.getView(),
                nodeEl = Ext.fly(view.getNodeByRecord(node));

            if(formFieldData.isValidator === true ) {
                fieldName = 'v.' + fieldName;
            }

            if(nodeEl) {
                nodeEl.removeCls('tree_node_error');
            }

            if(!node.data.object.isValid()) {
                nodeEl.addCls('tree_node_error');
                throw t('form_builder_form_type_invalid')
            }
        }

        formFieldData.fields = null;

        if (node.childNodes.length > 0) {
            formFieldData.fields = [];
            for (var i = 0; i < node.childNodes.length; i++) {
                formFieldData.fields.push(this.getNodeData(node.childNodes[i]));
            }
        }

        return formFieldData;
    },

    showImportPanel: function() {

        var importPanel = new Formbuilder.comp.importer(this);
        importPanel.showPanel();

    },

    importForm: function(importedFormData) {

        this.parentPanel.getEditPanel().removeAll();

        this.formId = importedFormData.id;
        this.formName = importedFormData.name;
        this.formDate = importedFormData.date;
        this.formConfig = array_merge(this.formConfig, importedFormData.config);

        this.addLayout();
        this.initLayoutFields();
    },

    exportForm: function() {
        location.href = '/admin/formbuilder/settings/get-export-file/' + this.formId + '/' + this.formName;
    },

    rootFormIsValid : function() {

        var isValid = true;

        if( this.rootFields.length > 0 ) {
            this.rootFields.each(function(field) {
                if( typeof field.getValue === 'function') {
                    try {
                        if(field.getValue() === '') {
                            isValid = false;
                            return false;
                        }

                    } catch(e) {
                        console.warn(field, e);
                    }
                }
            });
        }

        if( this.formName.length <= 2 || in_array(this.formName.toLowerCase(), this.parentPanel.forbiddennames)) {
            isValid = false;
        }

        return isValid;

    },

    save: function(ev) {

        var formData = {};

        this.saveCurrentNode();

        if(this.formIsValid) {

            this.tree.getRootNode().set('cls', '');

            try {
                formData = this.getData();
            } catch (e) {
                Ext.MessageBox.alert(t('error'), e);
                return false;
            }

            var formConfig = Ext.encode(this.formConfig),
                formFields = Ext.encode(formData);

            Ext.Ajax.request({
                url: '/admin/formbuilder/settings/save-form',
                method: 'post',
                params: {
                    form_id: this.formId,
                    form_name: this.formName,
                    form_config: formConfig,
                    form_fields: formFields
                },
                success: this.saveOnComplete.bind(this),
                failure: this.saveOnError.bind(this)
            });

        } else {
            this.tree.getRootNode().set('cls', 'tree_node_error');
            Ext.Msg.alert(t('error'), t('form_builder_invalid_form_config'));
        }
    },

    saveOnComplete: function(response) {

        var res = Ext.decode(response.responseText);

        if( res.formId && res.formName ) {
            this.panel.setTitle( res.formName + ' (ID: ' + res.formId + ')');
        }

        this.parentPanel.tree.getStore().load();
        pimcore.helpers.showNotification(t('success'), t('form_builder_builder_saved_successfully'), 'success');

    },

    saveOnError: function() {
        pimcore.helpers.showNotification(t('error'), t('form_builder_some_fields_cannot_be_saved'), 'error');
    },

    /**
     * Helper: find duplicate form type names
     * @param node
     * @param nodeNames
     * @returns {Array}
     */
    getUsedFieldNames: function(node, nodeNames) {

        var formFieldData = {},
            nodeNames = nodeNames ? nodeNames : [];

        if(node.data.object) {

            formFieldData = node.data.object.getData();
            var fieldName = formFieldData.name;

            if(formFieldData.isValidator === true ) {
                fieldName = 'v.' + fieldName;
            }

            nodeNames.push(fieldName);
        }

        if (node.childNodes.length > 0) {
            for (var i = 0; i < node.childNodes.length; i++) {
                this.getUsedFieldNames(node.childNodes[i], nodeNames);
            }
        }

        return nodeNames;
    },

    getFormTypeStructure: function(typeId) {

        var formTypeElement = false;
        if (this.availableFormFields.length === 0) {
            return formTypeElement;
        }

        for (var i = 0; i < this.availableFormFields.length; i++) {
            var formGroup = this.availableFormFields[i];
            for (var groupI = 0; groupI < formGroup.fields.length; groupI++) {
                var formGroupElement = formGroup.fields[groupI];
                if(formGroupElement.type === typeId) {
                    formTypeElement = formGroupElement;
                    break;
                }
            }
        }

        return formTypeElement;
    }

});