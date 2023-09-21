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
    editors: {},
    additionalParameter: {},

    forceClose: false,
    configuration: null,
    editorData: null,
    mailType: null,

    autoSaveData: {},

    initialize: function (formId, identifier, additionalParameter, specificLocale, callbacks) {

        this.formId = formId;
        this.specificLocale = specificLocale ? specificLocale : null;
        this.callbacks = callbacks;
        this.additionalParameter = additionalParameter ? additionalParameter : {};

        this.editors = {};
        this.mailType = null;
        this.forceClose = false;
        this.selectionId = 'form_mail_selection_' + Ext.id();

        this.mailType = identifier;
        this.autoSaveData = {};

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
        if (this.editors[editorId]) {
            this.editors[editorId]['editor'].remove();
            delete this.editors[editorId];
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

        this.leftInnerPannel = new Ext.Panel({
            closable: false,
            border: false,
            title: false,
            autoScroll: true,
        });

        this.preFillButton = new Ext.Button({
            text: 'Prefill',
            anchor: '100%',
            style: 'margin: 5px 20px; width: 85%;',
            hidden: this.configuration.widgetFieldsTemplate === null,
            handler: function (btn) {

                if (!this.editors) {
                    return;
                }

                Ext.Object.each(this.editors, function (index, editorData) {
                    editorData.editor.setContent(this.configuration.widgetFieldsTemplate);
                }.bind(this));

            }.bind(this)
        });

        this.rightPanel = new Ext.Panel({
            closable: false,
            html: rightHtml,
            items: [
                this.preFillButton,
                {
                    xtype: 'container',
                    html: rightHtml,
                }
            ],
            border: false,
            flex: 1,
            height: 645,
            align: 'stretch',
            title: 'Fields',
            autoScroll: true
        });

        this.rightPanel.on('afterrender', this.initSelectionFields.bind(this));

        var mailTypeSelector = new Ext.form.ComboBox({
            fieldLabel: t('type'),
            value: 'html',
            displayField: 'label',
            valueField: 'value',
            mode: 'local',
            queryMode: 'local',
            labelAlign: 'left',
            triggerAction: 'all',
            anchor: '100%',
            editable: false,
            summaryDisplay: true,
            allowBlank: false,
            name: 'mailType',
            style: 'margin: 5px 0; padding: 0 10px;',
            store: new Ext.data.ArrayStore({
                fields: ['value', 'label'],
                data: [['html', 'HTML'], ['text', 'TEXT']]
            }),
            listeners: {
                change: function (combo, value, previousValue) {

                    this.preFillButton.setHidden(value === 'text');
                    this.autoSaveEditorData(previousValue)
                    this.leftInnerPannel.removeAll();
                    this.initializeLocalizedEditor(value);
                }.bind(this),
                render: function (combo) {
                    combo.getStore().load();
                }.bind(this),
            }
        });

        this.leftPanel.add([mailTypeSelector, this.leftInnerPannel]);
        this.editPanel.add([this.leftPanel, this.rightPanel]);
        this.detailWindow.add(this.editPanel);

        this.initializeLocalizedEditor('html');
    },

    initializeLocalizedEditor: function (type) {

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
                    afterrender: this.initEditor.bind(this, type, editorId, locale.toLowerCase()),
                    beforedestroy: this.onClose.bind(this, editorId),
                },
                html: oldHtml
            });
        }.bind(this));

        editorField = new Ext.form.FieldSet({
            cls: 'form_builder_mail_editor_localized_field',
            layout: 'anchor',
            hideLabel: false,
            border: false,
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

        this.leftInnerPannel.add(editorField);
    },

    initSelectionFields: function () {

        var selectionField;

        selectionField = Ext.get(this.selectionId);

        Ext.Array.each(this.configuration.widgetGroups, function (groupData) {
            var group;
            selectionField.createChild('<h4>' + groupData.label + '</h4>');
            group = selectionField.createChild('<ul class="group"></ul>');
            Ext.Array.each(groupData.elements, function (element) {

                var subTypeTag = element.subType !== null ? '<span class="tag">[' + element.subType + ']</span>' : '',
                    child = group.createChild('<li contenteditable="false" draggable="true">' + subTypeTag + '' + element.label + '</li>');

                child.dom.addEventListener('dragstart', function (event) {
                    event.dataTransfer.setData('element', JSON.stringify(element));
                });
            }.bind(this));

        }.bind(this));

    },

    initEditor: function (type, editorId, locale) {

        var editorInstance;

        if (this.editors.hasOwnProperty(editorId)) {
            return;
        }

        editorInstance = tinymce.init({
            icons: 'customIcons',
            height: 500,
            language: pimcore.settings['language'],
            resize: false,
            menubar: false,
            block_unsupported_drop: false,
            newline_behavior: 'linebreak',
            selector: '#' + editorId,
            plugins: type === 'html' ? 'code link lists table' : 'code',
            toolbar: type === 'html' ? 'bold italic | bullist numlist link | code | inserttable' : 'code',
            custom_elements: '~fb-field,fb-container-field',
            editable_root: false,
            forced_root_block: 'div',
            contextmenu: false,
            object_resizing: false,
            table_toolbar: 'rowoptions columnoptions | tabledelete',
            table_header_type: 'cells',
            table_default_styles: {},
            table_default_attributes: {},
            table_use_colgroups: false,
            table_appearance_options: false,
            table_advtab: false,
            table_row_advtab: false,
            table_cell_advtab: false,
            content_style: `
                
                .mce-content-body inline-token[contentEditable=false][data-mce-selected] {
                    outline: none;
                    cursor: default;
                }
                
                .mce-content-body conditional-block[contenteditable=false][data-mce-selected] {
                    outline: none;
                    cursor: default;
                    box-shadow: 0 0 0 3px #b4d7ff;
                }

                .mce-content-body *[contentEditable=false] *[contentEditable=true]:focus {
                    outline: none;
                }

                .mce-content-body *[contentEditable=false] *[contentEditable=true]:hover {
                    outline: none;
                }

                body {
                    max-width: 600px;
                    margin: 2rem auto;
                }

                a {
                    color: #2980b9;
                }

                conditional-block {
                    margin: 1rem -6px;
                }
                
                table {
                    width: 100%;
                }
                
                table td {
                    width: 50%;
                }
            `,
            setup: (editor) => {

                const fieldComponent = new FieldComponent(editor, type, this.configuration.widgetConfiguration, this.getWidgetFieldsAsList());
                const containerFieldComponent = new ContainerFieldComponent(editor, type, this.configuration.widgetConfiguration, this.getWidgetFieldsAsList());

                tinymce.IconManager.add('customIcons', {
                    icons: {
                        'table-column-options': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M19 4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14zM5 13v5h4v-5H5zm14 0h-4v5h4v-5zm0-7h-4v5h4V6zM5 11h4V6H5v5z"/></svg>',
                    }
                });

                editor.ui.registry.addMenuButton('inserttable', {
                    icon: 'table',
                    tooltip: 'Insert table',
                    fetch: function (callback) {
                        callback([
                            {
                                type: 'fancymenuitem',
                                fancytype: 'inserttable',
                                onAction: function (data) {
                                    editor.execCommand('mceInsertTable', false, {
                                        rows: data.numRows,
                                        columns: data.numColumns,
                                        options: {
                                            headerRows: 0
                                        }
                                    });
                                }
                            }
                        ]);
                    }
                });

                editor.ui.registry.addMenuButton('rowoptions', {
                    icon: 'table-row-properties',
                    tooltip: 'Row options',
                    fetch: (callback) => {
                        var items = [
                            {
                                type: 'menuitem',
                                text: 'Insert row before',
                                icon: 'table-insert-row-above',
                                shortcut: 'alt+↑',
                                onAction: () => {
                                    menuActionManager.insertRowBefore();
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Insert row after',
                                icon: 'table-insert-row-after',
                                shortcut: 'alt+↓',
                                onAction: () => {
                                    menuActionManager.insertRowAfter();
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Delete row',
                                icon: 'table-delete-row',
                                onAction: () => {
                                    menuActionManager.deleteRow();
                                }
                            }
                        ];
                        callback(items);
                    }
                });

                editor.ui.registry.addMenuButton('columnoptions', {
                    icon: 'table-column-options',
                    tooltip: 'Column options',
                    fetch: (callback) => {
                        var items = [
                            {
                                type: 'menuitem',
                                text: 'Insert column before',
                                icon: 'table-insert-column-before',
                                shortcut: 'alt+←',
                                onAction: () => {
                                    menuActionManager.insertColumnBefore();
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Insert column after',
                                icon: 'table-insert-column-after',
                                shortcut: 'alt+→',
                                onAction: () => {
                                    menuActionManager.insertColumnAfter();
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Delete column',
                                icon: 'table-delete-column',
                                onAction: () => {
                                    menuActionManager.deleteColumn();
                                }
                            }
                        ];
                        callback(items);
                    }
                });

                const menuActionManager = {
                    insertRowBefore: () => {
                        tinymce.activeEditor.execCommand('mceTableInsertRowBefore');
                    },
                    insertRowAfter: () => {
                        tinymce.activeEditor.execCommand('mceTableInsertRowAfter');
                    },
                    deleteRow: () => {
                        tinymce.activeEditor.execCommand('mceTableDeleteRow');
                    },
                    insertColumnBefore: () => {
                        tinymce.activeEditor.execCommand('mceTableInsertColBefore');
                    },
                    insertColumnAfter: () => {
                        tinymce.activeEditor.execCommand('mceTableInsertColAfter');
                    },
                    deleteColumn: () => {
                        tinymce.activeEditor.execCommand('mceTableDeleteCol');
                    }
                }

                editor.on('init', () => {

                    if (this.editorData !== null && typeof this.editorData === 'object' && this.editorData.hasOwnProperty(locale)) {
                        if (this.editorData[locale].hasOwnProperty(type)) {
                            editor.setContent(this.editorData[locale][type]);
                        }
                    }

                    this.editors[editorId] = {
                        type: type,
                        editor: editor,
                        locale: locale
                    };

                });
            }
        });
    },

    loadEditorData: function () {

        var loadSuccess = function (data) {

            var parsedEditorData = {};

            this.detailWindow.setLoading(false);
            this.configuration = data.configuration;
            this.editorData = this.callbacks.loadData();

            if (this.editorData !== null && typeof this.editorData === 'object') {

                Ext.Object.each(this.editorData, function (locale, editorData) {

                    var editorContent;

                    // legacy
                    if (Ext.isString(this.editorData[locale])) {
                        editorContent = {
                            text: this.editorData[locale]
                        }
                    } else {
                        editorContent = this.editorData[locale];
                    }

                    parsedEditorData[locale] = editorContent;

                }.bind(this));

                this.editorData = parsedEditorData;
            }

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

        var editorSaveData = this.editorData !== null && typeof this.editorData === 'object' ? this.editorData : {};

        Ext.Object.each(this.editors, function (index, editorData) {

            if (!editorSaveData.hasOwnProperty(editorData['locale'])) {
                editorSaveData[editorData['locale']] = {};
            }

            editorSaveData[editorData['locale']][editorData['type']] = editorData['editor'].getContent();
        });

        this.callbacks.saveData(editorSaveData);

        if (typeof callback === 'function') {
            callback();
        }
    },

    autoSaveEditorData: function (type) {

        var editorSaveData = this.editorData !== null && typeof this.editorData === 'object' ? this.editorData : {};

        Ext.Object.each(this.editors, function (index, editorData) {

            if (!editorSaveData.hasOwnProperty(editorData['locale'])) {
                editorSaveData[editorData['locale']] = {};
            }

            editorSaveData[editorData['locale']][type] = editorData['editor'].getContent();
        });

        this.editorData = editorSaveData;
    },

    getWidgetFieldsAsList: function () {

        var widgetFields = [];

        Ext.Array.each(this.configuration.widgetGroups, function (groupData) {
            widgetFields = Ext.Array.merge(this.getWidgetFieldList(groupData.elements), widgetFields);
        }.bind(this));

        return widgetFields;
    },

    getWidgetFieldList: function (elements) {

        var widgetFields = [];

        Ext.Array.each(elements, function (element) {
            widgetFields.push(element);

            if (element.hasOwnProperty('children')) {
                widgetFields = Ext.Array.merge(this.getWidgetFieldList(element.children), widgetFields);
            }
        }.bind(this));

        return widgetFields;
    }
});