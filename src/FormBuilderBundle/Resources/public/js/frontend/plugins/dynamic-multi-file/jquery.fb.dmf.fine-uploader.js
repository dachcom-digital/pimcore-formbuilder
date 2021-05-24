/*
 *  Project: PIMCORE FormBuilder
 *  Extension: Dynamic Multi File | Fine Uploader
 *  Since: 3.4.0
 *  Author: DACHCOM.DIGITAL
 *  License: GPLv3
*/
;(function ($, window) {

    'use strict';

    function FormBuilderDynamicMultiFileFineUploader() {
        return this;
    }

    $.extend(FormBuilderDynamicMultiFileFineUploader.prototype, {

        init: function ($form, $dmfFields, dataUrls, options) {

            if (this.initialized === true) {
                return;
            }

            this.$form = $form;
            this.$dmfFields = $dmfFields;
            this.dataUrls = dataUrls;
            this.options = options;
            this.initialized = true;

            this.prepareLibrary();
        },

        prepareLibrary: function () {

            if (jQuery().fineUploader !== undefined) {
                this.prepareForm();
                return;
            }

            if (typeof this.options.libPath === 'undefined') {
                return;
            }

            $.getScript(this.options.libPath, function (data, textStatus, jqxhr) {
                if (jqxhr.status === 200) {
                    this.prepareForm();
                }
            }.bind(this));

        },

        prepareForm: function () {
            this.addListener();
            this.$dmfFields.each(this.setupFineUploaderElement.bind(this));
        },

        setupFineUploaderElement: function (index, el) {

            var _ = this,
                $el = $(el),
                $submitButton = this.$form.find('*[type="submit"]'),
                $template = $el.find('.qq-uploader-wrapper:first'),
                $element = $el.find('.qq-upload-container'),
                fieldId = $el.data('field-id'),
                storageFieldId = fieldId + '_data',
                $storageField = this.$form.find('input[type="hidden"][id="' + storageFieldId + '"]'),
                config = $el.data('engine-options'),
                fineUploadConfiguration;

            fineUploadConfiguration = {
                debug: false,
                template: $template,
                element: $element,
                messages: config.messages.core,
                text: {
                    formatProgress: config.messages.text.formatProgress,
                    failUpload: config.messages.text.failUpload,
                    waitingForResponse: config.messages.text.waitingForResponse,
                    paused: config.messages.text.paused
                },
                chunking: {
                    enabled: true,
                    concurrent: {
                        enabled: true
                    },
                    success: {
                        endpoint: _.getDataUrl('file_chunk_done')
                    }
                },
                request: {
                    endpoint: _.getDataUrl('file_add')
                },
                deleteFile: {
                    confirmMessage: config.messages.delete.confirmMessage,
                    deletingStatusText: config.messages.delete.deletingStatusText,
                    deletingFailedText: config.messages.delete.deletingFailedText,
                    enabled: true,
                    endpoint: _.getDataUrl('file_delete')
                },
                validation: {
                    sizeLimit: config.max_file_size,
                    allowedExtensions: config.allowed_extensions,
                    itemLimit: config.item_limit
                },
                callbacks: {
                    onUpload: function () {
                        $submitButton.attr('disabled', 'disabled');
                    },
                    onComplete: function (id, name, data) {

                        $submitButton.attr('disabled', false);

                        if (data.success === false) {
                            return;
                        }

                        this.addToStorageField($storageField, {
                            id: data.uuid,
                            fileName: data.fileName
                        });

                    }.bind(this),
                    onDeleteComplete: function (id, xhr) {
                        var data = jQuery.parseJSON(xhr.responseText);
                        this.removeFromStorageField($storageField, {
                            id: data.success === true ? data.uuid : data.path,
                        });
                    }.bind(this)
                }
            };

            this.$form.trigger('formbuilder.dynamic_multi_file.init', [$el, this.options, fineUploadConfiguration]);

            $el.fineUploader(fineUploadConfiguration);

            $template.remove();
        },

        addToStorageField: function ($storage, newData) {

            var data = typeof $storage.val() === 'string' && $storage.val() !== ''
                ? $.parseJSON($storage.val())
                : [];

            data.push(newData);

            $storage.val(JSON.stringify(data));
        },

        removeFromStorageField: function ($storage, newData) {

            var position,
                data = typeof $storage.val() === 'string' && $storage.val() !== ''
                    ? $.parseJSON($storage.val())
                    : [];

            position = $.map(data, function (block) {
                return block.id
            }).indexOf(newData.id);

            if (position === -1) {
                return;
            }

            data.splice(position, 1)

            $storage.val(JSON.stringify(data));
        },

        addListener: function () {

            this.$form.on({
                'submit': function (ev) {

                    var $instances = this.$form.find('.qq-upload-delete');

                    if ($instances.length === 0) {
                        return;
                    }

                    $instances.hide();

                }.bind(this),
                'formbuilder.request-done': function (ev) {

                    var $instances = this.$form.find('.qq-upload-delete');

                    if ($instances.length === 0) {
                        return;
                    }

                    $instances.show();

                }.bind(this),
                'formbuilder.success': function (ev) {

                    var $instances = this.$form.find('[data-dynamic-multi-file-instance]');

                    if ($instances.length === 0) {
                        return;
                    }

                    $instances.each(function (index, el) {
                        var $el = $(el),
                            fieldId = $el.data('field-id'),
                            storageFieldId = fieldId + '_data',
                            $storageField = this.$form.find('input[type="hidden"][id="' + storageFieldId + '"]');
                        $el.fineUploader('reset');
                        $storageField.val('');
                    }.bind(this));

                }.bind(this),
                'formbuilder.layout.post.add': function (ev, layout) {

                    var $el = $(layout),
                        $instances;

                    $instances = $el.find('[data-dynamic-multi-file-instance]');
                    if ($instances.length === 0) {
                        return;
                    }

                    $instances.each(this.setupFineUploaderElement.bind(this));

                }.bind(this),
                'formbuilder.layout.pre.remove': function (ev, layout) {

                    var $el = $(layout),
                        $uploadFields;

                    $uploadFields = $el.find('[data-dynamic-multi-file-instance]');
                    if ($uploadFields.length === 0) {
                        return;
                    }

                    $uploadFields.each(function (index, el) {

                        var $el = $(el),
                            fuInstance = $el.data('fineuploader'),
                            lockedStates = [qq.status.QUEUED, qq.status.UPLOADING, qq.status.DELETING, qq.status.UPLOAD_SUCCESSFUL],
                            config = $el.data('engine-options');

                        if (fuInstance && fuInstance.uploader.getUploads({status: lockedStates}).length > 0) {
                            throw new Error(config.messages.global.cannotDestroyActiveInstanceError);
                        }
                    });
                }
            });
        },

        getDataUrl: function (section) {
            return this.dataUrls[section];
        }
    });

    // window instance requires to be called "formBuilderDynamicMultiFileHandler"
    window.formBuilderDynamicMultiFileHandler = new FormBuilderDynamicMultiFileFineUploader();

})(jQuery, window);