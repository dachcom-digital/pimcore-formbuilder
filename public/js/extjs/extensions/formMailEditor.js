pimcore.registerNS('Formbuilder.extjs.extensions.formMailEditor');
Formbuilder.extjs.extensions.formMailEditor = Class.create({

    formId: null,
    specificLocale: null,
    callbacks: null,
    fields: {},
    selectionWindow: null,
    detailWindow: null,
    editPanel: null,
    selectionId: null,
    ckEditors: {},
    additionalParameter: {},

    forceClose: false,
    configuration: null,
    editorData: null,
    mailType: null,

    initialize: function (formId, identifier, additionalParameter, specificLocale, callbacks) {

        this.formId = formId;
        this.specificLocale = specificLocale ? specificLocale : null;
        this.callbacks = callbacks;
        this.additionalParameter = additionalParameter ? additionalParameter : {};

        this.ckEditors = {};
        this.mailType = null;
        this.forceClose = false;
        this.selectionId = 'form_mail_selection_' + Ext.id();

        this.mailType = identifier;
        this.loadMailEditor();
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
            title: t('form_builder.mail_editor.close_confirmation_title'),
            msg: t('form_builder.mail_editor.close_confirmation_message'),
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
        if (this.ckEditors[editorId]) {
            this.ckEditors[editorId]['editor'].destroy();
            delete this.ckEditors[editorId];
        }
    },

    loadMailEditor: function () {

        if (this.detailWindow !== null) {
            return this.detailWindow;
        }

        this.detailWindow = new Ext.Window({
            width: 1200,
            height: 768,
            iconCls: 'pimcore_icon_mail_editor',
            layout: 'fit',
            closeAction: 'destroy',
            plain: true,
            autoScroll: true,
            preventRefocus: true,
            cls: 'formbuilder-mail-editor',
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

    createPanel: function () {

        var rightHtml = '' +
            '<div class="mail-editor-selection" id="' + this.selectionId + '"></div>';

        this.editPanel = new Ext.Panel({
            closable: false,
            bodyStyle: 'position:relative;',
            border: false,
            layout: 'hbox',
            autoScroll: false
        });

        this.leftPanel = new Ext.Panel({
            closable: false,
            border: false,
            title: 'Editor' + ' (' + t('form_builder.mail_editor.mail_type_slug') + ': ' + t('form_builder.mail_editor.mail_type_' + this.mailType) + ')',
            layout: 'fit',
            flex: 3,
            align: 'stretch',
            autoScroll: false
        });

        this.rightPanel = new Ext.Panel({
            closable: false,
            html: rightHtml,
            border: false,
            flex: 1,
            height: 645,
            align: 'stretch',
            title: 'Fields',
            autoScroll: true
        });

        this.initCkEditorPlugins();
        this.initializeLocalizedEditor();

        this.rightPanel.on('afterrender', this.initSelectionFields.bind(this));

        this.editPanel.add([this.leftPanel, this.rightPanel]);
        this.detailWindow.add(this.editPanel);
    },

    initializeLocalizedEditor: function () {

        var tabs = [],
            editorField,
            locales,
            pimcoreLocales = Ext.isArray(pimcore.settings.websiteLanguages) ? pimcore.settings.websiteLanguages : [];

        if (this.specificLocale === null) {
            locales = Ext.Array.merge(['default'], pimcoreLocales)
        } else {
            locales = [this.specificLocale]
        }

        Ext.each(locales, function (locale) {

            var editorId = 'form_mail_editor_' + Ext.id(),
                oldHtml = '<div class="mail-editor" id="' + editorId + '"></div>';

            tabs.push({
                title: locale === 'default' ? t('default') : pimcore.available_languages[locale],
                iconCls: locale === 'default' ? 'pimcore_icon_white_flag' : 'pimcore_icon_language_' + locale.toLowerCase(),
                layout: 'anchor',
                height: 560,
                listeners: {
                    afterrender: this.initCkEditor.bind(this, editorId, locale.toLowerCase()),
                    beforedestroy: this.onClose.bind(this, editorId),
                },
                html: oldHtml
            });
        }.bind(this));

        editorField = new Ext.form.FieldSet({
            cls: 'form_builder_mail_editor_localized_field',
            layout: 'anchor',
            hideLabel: false,
            items: [{
                xtype: 'tabpanel',
                activeTab: 0,
                layout: 'anchor',
                width: '100%',
                defaults: {
                    autoHeight: true,
                },
                items: tabs
            }]
        });

        this.leftPanel.add(editorField);

    },

    initSelectionFields: function () {

        var selectionField;

        selectionField = Ext.get(this.selectionId);
        selectionField.createChild('<span><strong>Attention! </strong>This mail editor does not respect any special mail template language (like inky)!<br><br></span>');

        Ext.Array.each(this.configuration.widgetGroups, function (groupData) {
            var group;
            selectionField.createChild('<h4>' + groupData.label + '</h4>');
            group = selectionField.createChild('<ul class="group"></ul>');
            Ext.Array.each(groupData.elements, function (element) {
                var subTypeTag = element.subType !== null ? '<span class="tag">[' + element.subType + ']</span>' : '';
                group.createChild('<li draggable="true" data-type="' + element.type + '" data-sub-type="' + element.subType + '">' + subTypeTag + '' + element.label + '</li>');
            }.bind(this));

        }.bind(this));

    },

    initCkEditor: function (editorId, locale) {

        var editorConfig, ckEditor;

        if (this.ckEditors.hasOwnProperty(editorId)) {
            return;
        }

        editorConfig = {
            height: 490,
            language: pimcore.settings['language'],
            resize_enabled: false,
            entities: false,
            entities_greek: false,
            entities_latin: false,
            baseFloatZIndex: 40000,
            enterMode: CKEDITOR.ENTER_BR,
            tabSpaces: 0,
            _formMailEditorWidgetFields: this.getWidgetFieldsAsList(),
            _formMailEditorConfiguration: this.configuration.widgetConfiguration,
            extraPlugins: 'formmaileditor,sourcedialog',
            toolbar: [
                ['Sourcedialog'],
            ]
        };

        ckEditor = CKEDITOR.appendTo(editorId, editorConfig);

        if (this.editorData !== null && typeof this.editorData === 'object' && this.editorData.hasOwnProperty(locale)) {
            ckEditor.setData(this.editorData[locale]);
        }

        ckEditor.on('instanceReady', function (ev) {
            var $el = CKEDITOR.document.getById(this.selectionId);

            $el.on('dragstart', function (evt) {
                var target, dataTransfer;
                target = evt.data.getTarget().getAscendant('li', true);
                CKEDITOR.plugins.clipboard.initDragDataTransfer(evt);
                dataTransfer = evt.data.dataTransfer;
                dataTransfer.setData('mail_editor_widget', {type: target.data('type'), subType: target.data('sub-type')});
                dataTransfer.setData('text/html', target.getText());
            }.bind(this));

        }.bind(this));

        this.ckEditors[editorId] = {'editor': ckEditor, 'locale': locale};
    },

    loadEditorData: function () {

        var loadSuccess = function (data) {
            this.editorData = this.callbacks.loadData();
            this.configuration = data.configuration;
            this.detailWindow.setLoading(false);
            this.createPanel();
        }.bind(this);

        this.detailWindow.setLoading(true);

        Ext.Ajax.request({
            url: '/admin/formbuilder/mail-editor/load',
            params: {
                id: this.formId,
                mailType: this.mailType,
                additionalParameter: this.additionalParameter
            },
            success: function (resp) {
                var data = Ext.decode(resp.responseText);
                loadSuccess(data);
            }.bind(this)
        });
    },

    saveEditorDataAndClose: function () {
        this.saveEditorData(null, null, function () {
            this.forceClose = true;
            this.detailWindow.close();
        }.bind(this));
    },

    saveEditorData: function (el, ev, callback) {

        var data = {};

        Ext.Object.each(this.ckEditors, function (index, editorData) {
            data[editorData['locale']] = editorData['editor'].getData();
        });

        this.callbacks.saveData(data);

        if (typeof callback === 'function') {
            callback();
        }
    },

    initCkEditorPlugins: function () {
        Ext.Array.each(['widget', 'widgetselection', 'lineutils', 'formmaileditor', 'notification'], function (pluginName) {
            if (CKEDITOR.plugins.get(pluginName) === null) {
                CKEDITOR.plugins.addExternal(pluginName, '/bundles/formbuilder/js/extjs/ckeditor/' + pluginName + '/', 'plugin.js');
            }
        });
    },

    getWidgetFieldsAsList: function () {

        var widgetFields = [];

        Ext.Array.each(this.configuration.widgetGroups, function (groupData) {
            Ext.Array.each(groupData.elements, function (element) {
                widgetFields.push(element);
            });
        });

        return widgetFields;
    },
});