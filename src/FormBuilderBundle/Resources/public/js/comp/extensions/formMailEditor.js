pimcore.registerNS('Formbuilder.comp.extensions.formMailEditor');
Formbuilder.comp.extensions.formMailEditor = Class.create({

    formId: null,
    fields: {},
    detailWindow: null,
    editPanel: null,
    editorId: null,
    selectionId: null,
    ckEditor: null,

    configuration: null,
    editorData: null,

    initialize: function (formId) {
        this.formId = formId;
        this.editorId = 'form_mail_editor_' + Ext.id();
        this.selectionId = 'form_mail_selection_' + Ext.id();

        this.getInputWindow();
        this.detailWindow.show();

        this.loadEditorData();
    },

    checkClose: function (win) {

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

    onClose: function () {

        if (this.ckEditor) {
            this.ckEditor.destroy();
            this.ckEditor = null;
        }
    },

    getInputWindow: function () {

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
                beforeClose: this.checkClose
            },
            buttons: [
                {
                    text: t('save'),
                    iconCls: 'pimcore_icon_save',
                    handler: this.saveEditorData.bind(this)
                },
                {
                    text: t('close'),
                    iconCls: 'pimcore_icon_empty',
                    handler: function () {
                        this.detailWindow.close();
                    }.bind(this)
                }
            ]
        });
    },

    createPanel: function () {

        var htmlData = '' +
            '<div class="mail-editor-columns">' +
            '<div class="mail-editor" id="' + this.editorId + '"></div>' +
            '<div class="mail-editor-selection" id="' + this.selectionId + '"></div>' +
            '</div>';

        this.editPanel = new Ext.Panel({
            closable: false,
            bodyStyle: 'position:relative;',
            html: htmlData,
            border: false,
            layout: 'fit',
            autoScroll: false
        });

        this.editPanel.on('afterlayout', this.initCkEditor.bind(this));
        this.editPanel.on('beforedestroy', this.onClose.bind(this));

        this.detailWindow.add(this.editPanel);
    },

    initCkEditor: function () {

        var editorConfig, selectionField;

        if (this.ckEditor) {
            return;
        }

        this.initCkEditorPlugin();

        selectionField = Ext.get(this.selectionId);
        Ext.Array.each(this.configuration.widgetGroups, function (groupData) {
            var group;
            selectionField.createChild('<h4>' + groupData.label + '</h4>');
            group = selectionField.createChild('<ul class="group"></ul>');
            Ext.Array.each(groupData.elements, function (element) {
                var identifierTag = element.identifier !== element.type ? '<span class="tag">[' + element.identifier + ']</span>' : '';
                group.createChild('<li draggable="true" data-identifier="' + element.identifier + '">' + element.label + '' + identifierTag + '</li>');
            }.bind(this));

        }.bind(this));

        editorConfig = {
            height: 614,
            language: pimcore.settings['language'],
            resize_enabled: false,
            entities: false,
            entities_greek: false,
            entities_latin: false,
            baseFloatZIndex: 40000,
            enterMode: CKEDITOR.ENTER_BR,
            tabSpaces: 0,
            extraPlugins: 'form_mail_editor_placeholder,sourcedialog',
            toolbar: [
                ['Sourcedialog'],
            ]
        };

        this.ckEditor = CKEDITOR.appendTo(this.editorId, editorConfig);

        this.ckEditor.setData(this.editorData);

        this.ckEditor.on('instanceReady', function (ev) {
            var $el = CKEDITOR.document.getById(this.selectionId);

            $el.on('dragstart', function (evt) {
                var target, dataTransfer;
                target = evt.data.getTarget().getAscendant('li', true);
                CKEDITOR.plugins.clipboard.initDragDataTransfer(evt);
                dataTransfer = evt.data.dataTransfer;
                dataTransfer.setData('mail_editor_field', this.findDataRelation(target.data('identifier')));
                dataTransfer.setData('text/html', target.getText());
            }.bind(this));

        }.bind(this));
    },

    findDataRelation: function (identifier) {
        var searchElement = {};
        Ext.Array.each(this.configuration.widgetGroups, function (groupData) {
            Ext.Array.each(groupData.elements, function (element) {
                if (element.identifier === identifier) {
                    searchElement = element;
                }
            });
        });

        return searchElement;
    },

    loadEditorData: function () {

        this.detailWindow.setLoading(true);

        Ext.Ajax.request({
            url: '/admin/formbuilder/mail-editor/load',
            params: {
                id: this.formId
            },
            success: function (resp) {
                var data = Ext.decode(resp.responseText);
                this.editorData = data.data;
                this.configuration = data.configuration;
                this.detailWindow.setLoading(false);
                this.createPanel();
            }.bind(this)
        });
    },

    saveEditorData: function () {

        this.editPanel.setLoading(true);

        Ext.Ajax.request({
            url: '/admin/formbuilder/mail-editor/save',
            params: {
                id: this.formId,
                data: this.ckEditor.getData()
            },
            success: function () {
                this.editPanel.setLoading(false);
            }.bind(this)
        });
    },

    initCkEditorPlugin: function () {

        var plugin = CKEDITOR.plugins.get('form_mail_editor_placeholder');

        if (plugin !== null) {
            return;
        }

        Ext.Array.each(['widget', 'widgetselection', 'lineutils'], function (pluginName) {
            if (CKEDITOR.plugins.get(pluginName) === null) {
                CKEDITOR.plugins.addExternal(pluginName, '/bundles/formbuilder/js/comp/ckeditor/' + pluginName + '/', 'plugin.js');
            }
        });

        CKEDITOR.plugins.add('form_mail_editor_placeholder', {
            requires: 'widget',

            onLoad: function () {
                CKEDITOR.addCss('.fme-placeholder{background-color:#ff0}');
            },
            init: function (editor) {

                editor.widgets.add('form_mail_editor_placeholder', {

                    template: '<span class="fme-placeholder">[[]]</span>',
                    pathName: 'form_mail_editor_placeholder',

                    downcast: function (el) {
                        return new CKEDITOR.htmlParser.text('[[' + this.data.name + ']]');
                    },

                    init: function () {
                        this.setData('name', this.element.getText().slice(2, -2));
                    },

                    data: function () {
                        this.element.setText('[[' + this.data.name + ']]');
                    },

                    getLabel: function () {
                        return this.editor.lang.widget.label.replace(/%1/, this.data.name + ' ' + this.pathName);
                    }
                });

                editor.on('paste', function (evt) {

                    var attributes = [],
                        renderedAttributes,
                        configElements,
                        mailEditorField;

                    mailEditorField = evt.data.dataTransfer.getData('mail_editor_field');

                    if (!mailEditorField) {
                        return;
                    }

                    configElements = mailEditorField.config ? mailEditorField.config : [];
                    Ext.Object.each(configElements, function (key, value) {
                        attributes.push(key + '="' + value + '"');
                    });

                    renderedAttributes = attributes.length > 0 ? (' ' + attributes.join(' ')) : '';
                    evt.data.dataValue = '<span class="fme-placeholder">[[' + mailEditorField.type + renderedAttributes + ']]</span>';

                });
            },
            afterInit: function (editor) {
                var placeholderReplaceRegex = /\[\[([^\[\]])+\]\]/g;

                editor.dataProcessor.dataFilter.addRules({
                    text: function (text, node) {
                        var dtd = node.parent && CKEDITOR.dtd[node.parent.name];

                        if (dtd && !dtd.span)
                            return;

                        return text.replace(placeholderReplaceRegex, function (match) {
                            var widgetWrapper,
                                innerElement = new CKEDITOR.htmlParser.element('span', {
                                    'class': 'fme-placeholder'
                                });

                            innerElement.add(new CKEDITOR.htmlParser.text(match));
                            widgetWrapper = editor.widgets.wrapElement(innerElement, 'form_mail_editor_placeholder');

                            return widgetWrapper.getOuterHtml();
                        });
                    }
                });
            }
        });
    }
});