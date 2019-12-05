'use strict';

/*
 *  Project: PIMCORE FormBuilder
 *  Extension: Core
 *  Since: 2.2.0
 *  Author: DACHCOM.DIGITAL
 *  License: GPLv3
 *
 * Event Usage
 *
 * $('form.ajax-form').on('formbuilder.success', function(ev, messages, redirect, $form) {
 *     console.log(messages, redirect);
 * }).on('formbuilder.error', function(ev, messages, $form) {
 *     console.log(messages);
 * }).on('formbuilder.error-field', function(ev, data, $form) {
 *     console.log(data.field, data.messages);
 * });
*/

export class Formbuilder {
    constructor(el) {
        this.$form = el;
        this.window = window;
        this.document = document;
        this.defaults = {
            setupFileUpload: false,
            validationTransformer: {},
            resetFormMethod: null
        };
        this.formMethod = 'post';
        this.validationTransformer = null;
    }

    ValidationTransformer(options, formTemplate) {
        this.userMethods = options;
        this.formTemplate = formTemplate;
        this.themeTransform = {
            'bootstrap3': {
                addValidationMessage: function ($fields, messages) {
                    let $field = $fields.first(),
                        $formGroup = $field.closest('.form-group');

                    $formGroup.addClass('has-error');
                    $formGroup.find('span.help-block.validation').remove();

                    $.each(messages, function (validationType, message) {
                        let $spanEl = $('<span/>', {'class': 'help-block validation', 'text': message});
                        if ($fields.length > 1) {
                            $field.closest('label').before($spanEl);
                        } else {
                            $field.before($spanEl);
                        }
                    });
                },
                removeFormValidations: function ($form) {
                    $form.find('.help-block.validation').remove();
                    $form.find('.form-group').removeClass('has-error');
                }
            },
            'bootstrap4': {
                addValidationMessage: function ($fields, messages) {
                    let $field = $fields.first(),
                        $formGroup = $field.closest('.form-group'),
                        isDiv = $field.prop('nodeName') === 'DIV',
                        isMultipleInputElement = false;

                    $fields.addClass('is-invalid');

                    $formGroup.each(function () {
                        $(this).find('span.invalid-feedback.validation').remove();
                    });

                    if (isDiv === true) {
                        isMultipleInputElement = $field.find('input:checkbox,input:radio').length > 0;
                    }

                    if (isMultipleInputElement) {
                        $field.find('input:checkbox,input:radio').attr('required', 'required');
                    }

                    $field.closest('form').addClass('was-validated');

                    $.each(messages, function (validationType, message) {
                        let $spanEl = $('<span/>', {'class': 'invalid-feedback validation', 'text': message});

                        // multiple radio / checkbox:
                        // at least one checked strategy: add feedback message out of a single element
                        if (isMultipleInputElement) {
                            $field.addClass('fb-multiple-input-validated');
                            $field.append($spanEl.addClass('d-block'));
                        } else {
                            $fields.after($spanEl);
                        }
                    });
                },
                removeFormValidations: function ($form) {
                    let $multipleValidatedInputElements;

                    $form.removeClass('was-validated');
                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('span.invalid-feedback.validation').remove();

                    // multiple radio / checkbox:
                    // at least one checked strategy: add feedback message out of a single element
                    $multipleValidatedInputElements = $form.find('.fb-multiple-input-validated');
                    if ($multipleValidatedInputElements.length > 0) {
                        $multipleValidatedInputElements.removeClass('fb-multiple-input-validated');
                        $multipleValidatedInputElements.find('input:checkbox,input:radio').removeAttr('required');
                    }
                }
            },
            'divLayout': {
                addValidationMessage: function ($fields, messages) {
                    let $field = $fields.first(),
                        $formGroup = $field.closest('.form__item'),
                        isDiv = $field.prop('nodeName') === 'DIV',
                        isMultipleInputElement = false;

                    $fields.addClass('is-invalid');

                    $formGroup.each(function () {
                        $(this).addClass('has-error');
                        $(this).find('span.invalid-feedback.validation').remove();
                    });

                    if (isDiv === true) {
                        isMultipleInputElement = $field.find('input:checkbox,input:radio').length > 0;
                    }

                    if (isMultipleInputElement) {
                        $field.find('input:checkbox,input:radio').attr('required', 'required');
                    }

                    $field.closest('form').addClass('was-validated');

                    $.each(messages, function (validationType, message) {
                        let $spanEl = $('<span/>', {'class': 'invalid-feedback validation', 'text': message});

                        // multiple radio / checkbox:
                        // at least one checked strategy: add feedback message out of a single element
                        if (isMultipleInputElement) {
                            $field.addClass('fb-multiple-input-validated');
                            $field.append($spanEl.addClass('d-block'));
                        } else {
                            $formGroup.after($spanEl);
                        }
                    });
                },
                removeFormValidations: function ($form) {
                    $form.removeClass('was-validated');
                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('.has-error').removeClass('has-error');
                    $form.find('span.invalid-feedback.validation').remove();
                }
            }
        };

        this.transform = function () {
            let args = Array.prototype.slice.call(arguments),
                action = args.shift();

            if (typeof this.userMethods[action] === 'function') {
                return this.userMethods[action].apply(null, args);
            }

            switch (this.formTemplate) {
                case 'bootstrap_3_layout':
                case 'bootstrap_3_horizontal_layout':
                    return this.themeTransform.bootstrap3[action].apply(null, args);
                case 'bootstrap_4_layout':
                case 'bootstrap_4_horizontal_layout':
                    return this.themeTransform.bootstrap4[action].apply(null, args);
                case 'form_div_layout':
                    return this.themeTransform.divLayout[action].apply(null, args);
                default:
                    console.warn('unknown validation transformer action.', action);
                    break;
            }
        };
    }

    FormBuilderAjaxManager(options) {
        this.formTemplate = this.$form.data('template');
        this.options = $.extend({}, this.defaults, options);
        this.ajaxUrls = {};
        this.validationTransformer = new this.ValidationTransformer(this.options.validationTransformer, this.formTemplate);

        window.formBuilderGlobalContext = {};

        this.init();
    }

    init() {
        this.setAjaxFileStructureUrls();
        this.formMethod = this.$form.attr('method');
        this.submitForms();
        if (this.options.checkOnChange) {
            this.changeForms();
        }
    }

    removeValidations() {
        this.validationTransformer.transform('removeFormValidations', this.$form);
    }

    submitForms() {
        this.loadForms('submit', this.$form);
    }

    changeForms() {
        this.loadForms('change', 'input.is-invalid');
    }

    loadForms(action, el) {
        let _ = this;
        let $form = this.$form;

        $form.on(action, el, function (ev) {
            ev.preventDefault();
            if (_.ajaxUrls.length === 0) {
                console.warn('formbuilder ajax url structure missing.');
            }

            let $btns = $form.find('.button'),
                $fbHtmlFile = $form.find('.dynamic-multi-file');


            $btns.attr('disabled', 'disabled');

            if ($fbHtmlFile.length > 0) {
                $form.find('.qq-upload-delete').hide();
            }

            $.ajax({
                type: _.formMethod,
                url: _.getAjaxFileUrl('form_parser'),
                data: (this.formMethod === 'get') ? new FormData($form.serialize()) : new FormData($form[0]),
                processData: ($form.attr('method') === 'get'),
                contentType: ($form.attr('method') === 'get') ? $form.attr('enctype') : false,
                success: function (response) {
                    let generalFormErrors = [];

                    $btns.attr('disabled', false);
                    _.removeValidations();

                    if ($fbHtmlFile.length > 0) {
                        $form.find('.qq-upload-delete').show();
                    }

                    if (response.success === false) {
                        if (typeof response.validation_errors === 'object' && Object.keys(response.validation_errors).length > 0) {
                            $.each(response.validation_errors, function (fieldId, messages) {
                                if (fieldId === 'general') {
                                    generalFormErrors = messages;
                                } else {
                                    let $fields = $form.find('[id="' + fieldId + '"]');

                                    //fallback for radio / checkbox
                                    if ($fields.length === 0) {
                                        $fields = $form.find('[id^="' + fieldId + '"]');
                                    }

                                    //fallback for custom fields (like ajax file, headline or snippet type)
                                    if ($fields.length === 0) {
                                        $fields = $form.find('[data-field-name*="' + fieldId + '"]');
                                    }

                                    if ($fields.length > 0) {
                                        _.validationTransformer.transform('addValidationMessage', $fields, messages);
                                        $form.trigger('formbuilder.error-field', [{
                                            'field': $fields.first(),
                                            'messages': messages
                                        },]);
                                    }
                                }
                            });

                            if (generalFormErrors.length > 0) {
                                $form.trigger('formbuilder.error-form', [generalFormErrors, $form]);
                            }

                        } else {
                            if (response.error) {
                                $form.trigger('formbuilder.fatal', [response, $form]);
                            } else {
                                $form.trigger('formbuilder.error', [response.messages, $form]);
                            }
                        }

                    } else {

                        $form.trigger('formbuilder.success', [response.messages, response.redirect, $form]);

                        if (typeof _.options.resetFormMethod === 'function') {
                            _.options.resetFormMethod.apply(null, $form);
                        } else {
                            $form.trigger('reset');
                            // in case conditional logic is active.
                            $form.find('input, textarea').trigger('change');
                        }

                        if ($fbHtmlFile.length > 0) {
                            $fbHtmlFile.fineUploader('reset');
                            $fbHtmlFile.find('input[type="hidden"]').val('');
                        }

                        // @TODO: recaptcha is currently not implemented
                        // if (typeof grecaptcha === 'object' && $form.find('.g-recaptcha:first').length > 0) {
                        //     grecaptcha.reset();
                        // }
                    }
                },
                error: function (response) {
                    console.error('Oh no! Something went wrong!', response);
                }
            });
        });
    }


    /**
     * Setup File Upload Field (fineUploader is required!)
     */
    setupDynamicMultiFile() {
        let _ = this;

        if (!this.options.setupFileUpload) {
            return;
        }

        if (jQuery().fineUploader === undefined) {
            console.warn('fine uploader not found. please include the js library!');
        }

        this.$form.find('.dynamic-multi-file').each(function () {

            let $el = $(this),
                $form = $el.closest('form'),
                $submitButton = $form.find('*[type="submit"]'),
                $template = $el.find('.qq-uploader-wrapper:first'),
                $element = $el.find('.qq-upload-container'),
                fieldName = $el.data('field-name'),
                $storeField = $el.find('input[type="hidden"]'),
                formId = parseInt($form.find('input[name*="formId"]').val()),
                config = window[fieldName + '_dmf_config'];


            $el.fineUploader({
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
                        endpoint: _.getAjaxFileUrl('file_chunk_done'),
                    }
                },
                request: {
                    endpoint: _.getAjaxFileUrl('file_add'),
                    params: {
                        formId: formId,
                        fieldName: fieldName
                    }
                },
                deleteFile: {
                    confirmMessage: config.messages.delete.confirmMessage,
                    deletingStatusText: config.messages.delete.deletingStatusText,
                    deletingFailedText: config.messages.delete.deletingFailedText,

                    enabled: true,
                    endpoint: _.getAjaxFileUrl('file_delete'),
                    params: {
                        formId: formId,
                        fieldName: fieldName
                    }
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
                        $storeField.val($storeField.val() + ',' + data.uuid);
                        $submitButton.attr('disabled', false);
                    },
                    onDeleteComplete: function (id, xhr) {
                        let data = jQuery.parseJSON(xhr.responseText);
                        if (data.success === true) {
                            $storeField.val($storeField.val().replace(',' + data.uuid, ''));
                        } else {
                            $storeField.val($storeField.val().replace(',' + data.path, ''));

                        }
                    }
                }
            });

            $template.remove();

        });
    }

    setAjaxFileStructureUrls() {
        const _ = this;
        if (window.formBuilderGlobalContext.length > 0) {
            this.ajaxUrls = window.formBuilderGlobalContext;
            return;
        }

        $.ajax({
            type: 'post',
            url: this.$form.data('ajax-structure-url'),
            success: function (response) {
                this.ajaxUrls = response;
                window.formBuilderGlobalContext = this.ajaxUrls;
                _.setupDynamicMultiFile();
            }.bind(this)
        });
    }

    getAjaxFileUrl(section) {
        return this.ajaxUrls[section];
    }

}
