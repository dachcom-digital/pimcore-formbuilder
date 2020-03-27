pimcore.registerNS('Formbuilder.extjs.extensions.formObjectMappingEditorWorker.fieldCollectionWorker');
Formbuilder.extjs.extensions.formObjectMappingEditorWorker.fieldCollectionWorker = Class.create({

    formId: null,
    classId: null,
    fieldCollectionKey: null,
    data: null,

    window: null,
    comboBox: null,
    node: null,

    hasValidData: false,
    formObjectTreeMapper: null,
    formObjectTreeMapperPanel: null,

    initialize: function (formId, classId, fieldCollectionKey, data) {

        this.formId = formId;
        this.classId = classId;
        this.fieldCollectionKey = fieldCollectionKey;
        this.data = data;

        this.hasValidData = false;

        if (data !== null) {
            if (data.hasOwnProperty('fieldCollectionClassKey')) {
                if (data.hasOwnProperty('fieldMapping') && Ext.isArray(data.fieldMapping) && data.fieldMapping.length > 0) {
                    this.hasValidData = true;
                }
            }
        }
    },

    getName: function () {
        return 'fieldCollectionWorker';
    },

    getConfigDialog: function (node, cb) {

        this.node = node;

        var firstTimeLoad = true,
            comboBox = new Ext.form.ComboBox({
                fieldLabel: t('form_builder.output_workflow.output_workflow_channel.object.fc_worker_fieldcollection'),
                displayField: 'label',
                valueField: 'key',
                mode: 'local',
                queryMode: 'local',
                labelAlign: 'left',
                value: null,
                triggerAction: 'all',
                anchor: '100%',
                labelWidth: 180,
                editable: false,
                summaryDisplay: true,
                allowBlank: false,
                name: 'fieldCollection',
                listeners: {
                    change: function (field, value) {
                        if (firstTimeLoad === true) {
                            firstTimeLoad = false;
                            return;
                        }
                        this.changeFieldCollection(value, true);
                    }.bind(this),
                    render: function (combo) {
                        combo.getStore().load();
                    }.bind(this),
                }
            }),
            store = new Ext.data.Store({
                autoLoad: false,
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/formbuilder/output-workflow/object/get-field-collection-types',
                    fields: ['label', 'key'],
                    extraParams: {
                        classId: this.classId,
                        fieldCollectionKey: this.fieldCollectionKey
                    },
                    reader: {
                        type: 'json',
                        rootProperty: 'types'
                    },
                },
                listeners: {
                    load: function (store, records) {

                        var initValue = null;

                        if (this.data !== null && this.data.hasOwnProperty('fieldCollectionClassKey')) {
                            initValue = this.data.fieldCollectionClassKey;
                        } else if (records.length === 1) {
                            initValue = records[0].get('key');
                        }

                        if (initValue !== null) {
                            comboBox.setValue(initValue);
                            this.changeFieldCollection(initValue);
                        }
                        firstTimeLoad = false;
                    }.bind(this)
                }
            });

        comboBox.setStore(store);

        this.comboBox = comboBox;

        this.window = new Ext.Window({
            width: 900,
            height: 500,
            iconCls: 'form_builder_output_workflow_channel_object_mapper',
            layout: 'fit',
            closeAction: 'destroy',
            plain: true,
            autoScroll: true,
            preventRefocus: true,
            cls: 'formbuilder-object-mapping-editor-fieldcollection',
            title: 'Field Collection Configurator',
            modal: true,
            items: [this.configPanel],
            buttons: [
                {
                    text: t('form_builder.output_workflow.apply_and_close'),
                    iconCls: 'form_builder_output_workflow_apply_data',
                    handler: this.commitData.bind(this, cb)
                }
            ]
        });

        this.window.addDocked({
            xtype: 'toolbar',
            dock: 'top',
            items: [comboBox]
        });

        this.window.show();

        return this.window;
    },

    changeFieldCollection: function (fieldCollectionKey, switchLayout) {

        var pimcoreClassType = 'fieldcollection',
            parentNode = this.node.parentNode,
            containerFields = parentNode.data.omContainerFields,
            values = this.data && this.data.hasOwnProperty('fieldMapping') ? this.data.fieldMapping : null;

        if (this.formObjectTreeMapperPanel !== null) {
            this.window.remove(this.formObjectTreeMapperPanel);
        }

        if (switchLayout === true) {
            values = null;
        }

        this.formObjectTreeMapper = new Formbuilder.extjs.extensions.formObjectMappingEditorConfigurator.formObjectTreeMapper(
            this.formId,
            values,
            containerFields,
            pimcoreClassType,
            fieldCollectionKey,
            parentNode.data.text,
            parentNode.data.iconCls,
        );

        this.formObjectTreeMapperPanel = this.formObjectTreeMapper.getLayout();

        this.window.add(this.formObjectTreeMapperPanel);
    },

    isReadyToConfigure: function (node) {
        this.node = node;

        if (!this.node.parentNode) {
            return false;
        }

        return this.node.parentNode.data.omFieldTypeIdentifier === 'form_field';
    },

    isValid: function (node) {
        return this.hasValidData === true;
    },

    getData: function () {
        return this.data;
    },

    commitData: function (cb) {

        if (this.formObjectTreeMapper === null) {
            return;
        }

        this.hasValidData = this.formObjectTreeMapper.isValid();

        this.data = {
            'fieldCollectionClassKey': this.comboBox.getValue(),
            'fieldMapping': this.formObjectTreeMapper.getEditorData()
        };

        this.window.close();

        if (typeof cb === 'function') {
            cb();
        }
    }
});