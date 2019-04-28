(function () {
    CKEDITOR.plugins.add('formmaileditor', {
        requires: 'widget',

        onLoad: function () {
            CKEDITOR.addCss('.fme-placeholder{background-color:#ff0}');
        },

        init: function (editor) {

            CKEDITOR.dialog.add('formmaileditor', '/bundles/formbuilder/js/comp/ckeditor/formmaileditor/dialogs/placeholder.js');

            editor.widgets.add('formmaileditor', {

                dialog: 'formmaileditor',
                template: '<span class="fme-placeholder">[[]]</span>',
                pathName: 'formmaileditor',

                downcast: function (el) {
                    var attributes = '';
                    if (this.data.hasOwnProperty('additionalData')) {
                        Object.keys(this.data.additionalData).forEach(function (key) {
                            attributes += (' ' + key + '="' + this.data.additionalData[key] + '"');
                        }.bind(this));
                    }

                    return new CKEDITOR.htmlParser.text('[[' + this.data.attribute + attributes + ']]');
                },

                init: function () {

                    var attribute = this.element.getText().slice(2, -2),
                        typeData = attribute.split(' ');
                    var subType = null,
                        subTypeData = / sub-type="([^"]*)"/g.exec(attribute);

                    if (subTypeData !== null && subTypeData[1] !== undefined) {
                        subType = subTypeData[1];
                    }

                    this.setData('attribute', attribute);
                    this.setData('type', typeData[0]);
                    this.setData('subType', subType);
                },

                data: function () {
                    var attributes = '';
                    if (this.data.hasOwnProperty('additionalData')) {
                        Object.keys(this.data.additionalData).forEach(function (key) {
                            attributes += (' ' + key + '="' + this.data.additionalData[key] + '"');
                        }.bind(this));
                    }

                    this.element.setText('[[' + this.data.attribute + attributes + ']]');
                },

                getLabel: function () {
                    return this.editor.lang.widget.label.replace(/%1/, this.data.attribute + ' ' + this.pathName);
                }
            });

            editor.on('paste', function (evt) {

                var renderedSubType,
                    mailEditorWidget;

                mailEditorWidget = evt.data.dataTransfer.getData('mail_editor_widget');

                if (!mailEditorWidget) {
                    return;
                }

                renderedSubType = mailEditorWidget.subType !== 'null' ? (' sub-type="' + mailEditorWidget.subType + '"') : '';
                evt.data.dataValue = '<span class="fme-placeholder">[[' + mailEditorWidget.type + renderedSubType + ']]</span>';

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
                        widgetWrapper = editor.widgets.wrapElement(innerElement, 'formmaileditor');

                        return widgetWrapper.getOuterHtml();
                    });
                }
            });
        }
    });

    CKEDITOR.on('dialogDefinition', function (ev) {

        var dialogName = ev.data.name,
            dialogDefinition = ev.data.definition;

        if (dialogName !== 'formmaileditor') {
            return;
        }

        var config = ev.editor.config._formMailEditorConfiguration,
            widgetFields = ev.editor.config._formMailEditorWidgetFields,
            tableProperties = dialogDefinition.getContents('info');

        var isVisibleConfigField = function (widgetData, configIdentifier) {

            var isRelated = false;
            for (var i = 0; i < widgetFields.length; i++) {
                var widgetField = widgetFields[i];
                if (widgetField.type === widgetData.type && widgetField.subType === widgetData.subType && widgetField.configIdentifier === configIdentifier) {
                    isRelated = true;
                    break;
                }
            }

            return isRelated;

        }, parseAdditionalData = function (widgetData, configKey, data) {

            var additionalData = {};
            if (widgetData.hasOwnProperty('additionalData') && typeof widgetData.additionalData === 'object') {
                additionalData = widgetData.additionalData;
            }

            if (data === null || data === '') {
                if (additionalData.hasOwnProperty(configKey)) {
                    delete additionalData[configKey];
                }
            } else {
                additionalData[configKey] = data;
            }

            return additionalData;

        }, generateFieldKey = function (widgetData, configKey) {
            var type = widgetData.type,
                subType = widgetData.subType;

            if (subType === null) {
                return type + '_' + configKey;
            }

            return type + '_' + subType + '_' + configKey;
        };

        Object.keys(config).forEach(function (configIdentifier) {
            var configElements = config[configIdentifier];
            Object.keys(configElements).forEach(function (configKey) {
                var configElement = configElements[configKey];
                switch (configElement.type) {
                    case 'checkbox' :
                        tableProperties.add({
                            type: 'checkbox',
                            id: configIdentifier,
                            label: configElement.label,
                            'default': configElement.defaultValue === true,
                            setup: function (widget) {
                                var fieldName = generateFieldKey(widget.data, configKey);
                                if (widget.data.hasOwnProperty(fieldName)) {
                                    this.setValue(widget.data[fieldName]);
                                }
                                this.getElement().getParent().setStyle('display', isVisibleConfigField(widget.data, this.id) ? 'block' : 'none');
                            },
                            commit: function (widget) {
                                var fieldName = generateFieldKey(widget.data, configKey);
                                if (isVisibleConfigField(widget.data, this.id)) {
                                    widget.setData(fieldName, this.getValue());
                                    widget.setData('additionalData', parseAdditionalData(widget.data, configKey, this.getValue()));
                                }
                            }
                        });
                        break;
                    case 'input' :
                        tableProperties.add({
                            type: 'text',
                            id: configIdentifier,
                            label: configElement.label,
                            'default': configElement.defaultValue !== null ? configElement.defaultValue : '',
                            setup: function (widget) {
                                var fieldName = generateFieldKey(widget.data, configKey);
                                if (widget.data.hasOwnProperty(fieldName)) {
                                    this.setValue(widget.data[fieldName]);
                                }
                                this.getElement().getParent().setStyle('display', isVisibleConfigField(widget.data, this.id) ? 'block' : 'none');
                            },
                            commit: function (widget) {
                                var fieldName = generateFieldKey(widget.data, configKey);
                                if (isVisibleConfigField(widget.data, this.id)) {
                                    widget.setData(fieldName, this.getValue());
                                    widget.setData('additionalData', parseAdditionalData(widget.data, configKey, this.getValue()));
                                }
                            }
                        });
                        break;
                    case 'read-only' :
                        tableProperties.add({
                            type: 'text',
                            id: configIdentifier,
                            label: configElement.label,
                            'default': configElement.defaultValue !== null ? configElement.defaultValue : '',
                            setup: function (widget) {
                                var fieldName = generateFieldKey(widget.data, configKey);
                                if (widget.data.hasOwnProperty(fieldName)) {
                                    this.setValue(widget.data[fieldName]);
                                }
                                this.getElement().getParent().setStyle('display', isVisibleConfigField(widget.data, this.id) ? 'block' : 'none');
                                this.disable();
                            },
                            commit: function (widget) {
                                // nothing to do so far.
                            }
                        });
                        break;
                }
            });
        });
    });
})();
