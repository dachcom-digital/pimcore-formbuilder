pimcore.registerNS('Formbuilder.extjs.extensions.formApiMappingEditor');
Formbuilder.extjs.extensions.formApiMappingEditor = Class.create({

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

    formDataMapper: null,

    detailWindow: null,
    DragDropMgrNotifyOccluded: false,

    initialize: function (formId, additionalParameter, baseConfiguration, isLocal, callbacks) {

        this.formId = formId;
        this.additionalParameter = additionalParameter ? additionalParameter : {};
        this.baseConfiguration = baseConfiguration ? baseConfiguration : {};
        this.isLocal = isLocal === true;
        this.callbacks = callbacks;

        this.forceClose = false;

        this.loadDataMappingEditor();
    },

    checkClose: function (win) {

        if (this.forceClose === true) {
            win.closeMe = true;
            this.restoreNotifyOccludedState();
            return true;
        }

        if (win.closeMe) {
            win.closeMe = false;
            this.restoreNotifyOccludedState();
            return true;
        }

        Ext.Msg.show({
            title: t('form_builder.output_workflow.output_workflow_channel.api.close_confirmation_title'),
            msg: t('form_builder.output_workflow.output_workflow_channel.api.close_confirmation_message'),
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

    loadDataMappingEditor: function () {

        if (this.detailWindow !== null) {
            return this.detailWindow;
        }

        this.detailWindow = new Ext.Window({
            width: 800,
            height: 768,
            iconCls: 'form_builder_output_workflow_channel_api_mapper',
            layout: 'fit',
            closeAction: 'destroy',
            plain: true,
            autoScroll: true,
            preventRefocus: true,
            cls: 'formbuilder-api-mapping-editor',
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

        this.backupAndDisableNotifyOccludedState();
        this.loadEditorData();

    },

    /**
     * @see pimcore.document.edit.dnd
     * @see https://docs.sencha.com/extjs/6.0.1/classic/Ext.dd.DragDropManager.html#property-notifyOccluded
     *
     * This will lead to a drag and drop mismatch between multiple modal windows and tree panels.
     * To fix that, we need to reset the setting to false, as long our mapping editor is active.
     */
    backupAndDisableNotifyOccludedState: function () {
        this.DragDropMgrNotifyOccluded = Ext.dd.DragDropMgr.notifyOccluded;
        Ext.dd.DragDropMgr.notifyOccluded = false;
    },

    restoreNotifyOccludedState: function () {
        Ext.dd.DragDropMgr.notifyOccluded = this.DragDropMgrNotifyOccluded;
    },

    loadEditorData: function () {

        var loadSuccess = function (data) {
            this.editorData = this.isLocal ? this.callbacks.loadData() : data.data;
            this.configuration = data.configuration;
            this.detailWindow.setLoading(false);
            this.detailWindow.setTitle(data.configuration.apiProvider.label);
            this.createPanel();
        }.bind(this);

        this.detailWindow.setLoading(true);

        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/api/get-form-data',
            method: 'POST',
            params: {
                id: this.formId,
                baseConfiguration: Ext.encode(this.baseConfiguration),
                additionalParameter: this.additionalParameter,
                externalData: this.isLocal === true
            },
            success: function (resp) {
                var data = Ext.decode(resp.responseText);

                if (data.success === false) {
                    this.forceClose = true;
                    this.detailWindow.close();
                    Ext.Msg.alert(t('error'), data.message);
                    return;
                }

                loadSuccess(data);

            }.bind(this),
            failure: function (resp) {
                this.forceClose = true;
                this.detailWindow.close();
            }.bind(this),
        });
    },

    createPanel: function () {

        this.formDataMapper = new Formbuilder.extjs.extensions.formDataMappingEditor.formDataMapper(
            this.formId,
            this.editorData,
            this.configuration
        );

        this.detailWindow.add(this.formDataMapper.getLayout());

        this.detailWindow.addDocked({
            xtype: 'toolbar',
            dock: 'bottom',
            layout: {
                type: 'vbox',
                align: 'stretch',
            },
            items: [{
                xtype: 'label',
                style: 'display: block; margin-bottom:5px;',
                html: '<strong>Fieldset</strong>: ' + t('form_builder.output_workflow.output_workflow_channel.api.fieldset_mapping_info')
            },
                {
                    xtype: 'label',
                    style: 'display: block;',
                    html: '<strong>Repeater</strong>: ' + t('form_builder.output_workflow.output_workflow_channel.api.repeater_mapping_info')
                }]
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

        if (this.formDataMapper.isValid() === false) {
            Ext.Msg.alert(t('error'), t('form_builder.output_workflow.output_workflow_channel.api.editor.invalid_configuration'));
            return;
        }

        data = this.formDataMapper.getEditorData();

        if (this.isLocal === true) {
            this.callbacks.saveData(data);

            if (typeof callback === 'function') {
                callback();
            }
        }
    }
});