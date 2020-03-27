pimcore.registerNS('Formbuilder.extjs.extensions.formObjectMappingEditor');
Formbuilder.extjs.extensions.formObjectMappingEditor = Class.create({

    FIELD_TYPE_FORM_BUILDER: 'form_field',
    FIELD_TYPE_DATA_CLASS_FIELD: 'data_class_field',

    formId: null,
    additionalParameter: {},
    baseConfiguration: {},
    isLocal: null,
    callbacks: null,

    configuration: null,
    editorData: null,
    forceClose: false,

    formObjectTreeMapper: null,

    detailWindow: null,

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
                    text: t('form_builder.output_workflow.apply_and_close'),
                    iconCls: 'form_builder_output_workflow_apply_data',
                    handler: this.saveEditorDataAndClose.bind(this)
                },
                {
                    text: t('cancel'),
                    iconCls: 'pimcore_icon_cancel',
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

        this.formObjectTreeMapper = new Formbuilder.extjs.extensions.formObjectMappingEditorConfigurator.formObjectTreeMapper(
            this.formId,
            this.editorData,
            this.configuration.formFieldDefinitions,
            'object',
            this.configuration.classId
        );

        if (this.baseConfiguration.resolveStrategy === 'existingObject') {
            this.formObjectTreeMapper.setOnlyContainerElementsAllowed();
        }

        this.detailWindow.add(this.formObjectTreeMapper.getLayout());

        this.detailWindow.addDocked({
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
                {
                    xtype: 'label',
                    html: (
                        '<strong>' + t('form_builder.output_workflow.output_workflow_channel.object.resolve_strategy') + '</strong>: ' +
                        (
                            this.baseConfiguration.resolveStrategy === 'newObject'
                                ? t('form_builder.output_workflow.output_workflow_channel.object.resolve_with_new_object')
                                : t('form_builder.output_workflow.output_workflow_channel.object.resolve_with_existing_object')
                        )
                    )
                },
                {
                    xtype: 'label',
                    html:
                        '<strong>Data Class</strong>: ' +
                        this.configuration.className
                },
            ]
        });
    },

    saveEditorDataAndClose: function () {
        this.saveEditorData(null, null, function () {
            this.forceClose = true;
            this.detailWindow.close();
        }.bind(this));
    },

    saveEditorData: function (el, ev, callback) {

        var data;

        if (this.formObjectTreeMapper.isValid() === false) {
            Ext.Msg.alert(t('error'), t('form_builder.output_workflow.output_workflow_channel.object.editor.invalid_configuration'));
            return;
        }

        data = this.formObjectTreeMapper.getEditorData();

        if (this.isLocal === true) {
            this.callbacks.saveData(data);

            if (typeof callback === 'function') {
                callback();
            }
        }

        /**
         * @TODO: implement non-local persistence

         this.detailWindow.setLoading(true);

         Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/object/save-object-mapping-data',
            params: {
                id: this.formId,
                data: Ext.encode(data)
            },
            success: function (resp) {

                var data = Ext.decode(resp.responseText);
                this.detailWindow.setLoading(false);

                if (data.success === false) {
                    Ext.Msg.alert(t('error'), data.message);
                    return;
                }

                if (typeof callback === 'function') {
                    callback();
                }
            }.bind(this)
        });

         **/
    }
});