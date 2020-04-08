'use strict';

CKEDITOR.dialog.add('formmaileditor', function (editor) {
    var generalLabel = editor.lang.common.generalTab,
        validNameRegex = /^[^\[\]<>]+$/;

    return {
        title: 'Configuration',
        minWidth: 300,
        minHeight: 80,
        contents: [
            {
                id: 'info',
                label: generalLabel,
                title: generalLabel,
                elements: [
                    {
                        id: 'attribute',
                        type: 'text',
                        label: 'Attribute',
                        required: true,
                        validate: CKEDITOR.dialog.validate.regex(validNameRegex, 'invalid'),
                        setup: function (widget) {
                            this.setValue(widget.data.attribute);
                            this.disable();
                        },
                        commit: function (widget) {
                            widget.setData('attribute', this.getValue());
                        }
                    }
                ]
            }
        ]
    };
});