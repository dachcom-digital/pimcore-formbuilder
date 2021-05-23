/*
 *  Project: PIMCORE FormBuilder
 *  Extension: Dynamic Multi File | DropZone
 *  Since: 3.4.0
 *  Author: DACHCOM.DIGITAL
 *  License: GPLv3
*/
;(function ($, window) {

    'use strict';

    function FormBuilderDynamicMultiFileDropZone() {
        return this;
    }

    $.extend(FormBuilderDynamicMultiFileDropZone.prototype, {

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

            if (window.Dropzone !== undefined) {
                this.prepareForm();
                return;
            }

            if(typeof this.options.libPath === 'undefined') {
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
            this.$dmfFields.each(this.setupDropZoneElement.bind(this));
        },

        setupDropZoneElement: function (index, el) {

            var _ = this,
                $el = $(el),
                $submitButton = this.$form.find('*[type="submit"]'),
                $template = $el.find('.dropzone-template'),
                $element = $el.find('.dropzone-container'),
                fieldId = $el.data('field-id'),
                storageFieldId = fieldId + '_data',
                $storageField = this.$form.find('input[type="hidden"][id="' + storageFieldId + '"]'),
                config = $el.data('engine-options'),
                dropZoneConfiguration;

            $element.addClass('dropzone');

            dropZoneConfiguration = {
                paramName: 'dmfData',
                url: _.getDataUrl('file_add'),
                chunking: config.multiple === false,
                addRemoveLinks: true,
                hiddenInputContainer: $el[0],
                maxFiles: config.item_limit,
                acceptedFiles: config.allowed_extensions,
                maxFilesize: config.max_file_size,
                uploadMultiple: config.multiple,
                init: function () {

                    $template.remove();

                    this.on('removedfile', function (file) {
                        $.ajax({
                            type: 'DELETE',
                            url: _.getDataUrl('file_delete') + '/' + file.upload.uuid,
                            data: {
                                uploadStatus: file.status
                            },
                            success: function (response) {

                                if (response.success === false) {
                                    return;
                                }

                                _.removeFromStorageField($storageField, {
                                    id: response.uuid
                                });

                            }
                        });
                    });

                    this.on('sending', function (file, xhr, formData) {
                        $submitButton.attr('disabled', 'disabled');
                        formData.append('uuid', file.upload.uuid);
                    });

                    this.on('complete', function (file) {
                        $submitButton.attr('disabled', false);
                    });

                    this.on('success', function (file, response) {
                        _.addToStorageField($storageField, {
                            id: response.uuid,
                            fileName: response.fileName
                        });

                    }.bind(this));

                    this.on('canceled', function (file) {
                        $submitButton.attr('disabled', false);
                    });

                    _.$form.trigger('formbuilder.dynamic_multi_file.drop_zone.init', [this]);
                }
            };

            if ($template.children('div.dz-file-preview').length > 0) {
                dropZoneConfiguration.previewTemplate = $template.html();
            }

            if (config.translations) {
                dropZoneConfiguration = $.extend({}, dropZoneConfiguration, config.translations);
            }

            this.$form.trigger('formbuilder.dynamic_multi_file.init', [$el, this.options, dropZoneConfiguration]);

            $element.dropzone(dropZoneConfiguration);
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

                    var $instances = this.$form.find('.dz-remove');

                    if ($instances.length === 0) {
                        return;
                    }

                    $instances.hide();

                }.bind(this),
                'formbuilder.request-done': function (ev) {

                    var $instances = this.$form.find('.dz-remove');

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
                            dzInstance = null,
                            fieldId = $el.data('field-id'),
                            storageFieldId = fieldId + '_data',
                            $storageField = this.$form.find('input[type="hidden"][id="' + storageFieldId + '"]');

                        try {
                            dzInstance = Dropzone.forElement($el.find('.dropzone-container')[0])
                        } catch (e) {
                            console.log(e);
                        }

                        if (dzInstance !== null) {
                            dzInstance.removeAllFiles();
                        }

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

                    $instances.each(this.setupDropZoneElement.bind(this));

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
                            dzInstance = null,
                            config = $el.data('engine-options');

                        try {
                            dzInstance = Dropzone.forElement($el.find('.dropzone-container')[0])
                        } catch (e) {
                            console.log(e);
                        }

                        if (dzInstance !== null && dzInstance.files.length > 0) {
                            throw new Error(config.instance_error);
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
    window.formBuilderDynamicMultiFileHandler = new FormBuilderDynamicMultiFileDropZone();

})(jQuery, window);