var formBuilder = (function () {

    'use strict';

    var self = {

        ajaxUrls: {},

        $container : null,

        init: function ($container) {
            self.$container = $container !== undefined ? $container : $('form.formbuilder.ajax-form');
            self.startSystem();
        },

        startSystem: function () {

            this.setAjaxFileStructureUrls();
            this.loadForms();

        },

        loadForms: function() {

            var _ = this;

            /*

             // Use those Events in your Project!

             $('form.ajax-form')
               .on('formbuilder.success', function(ev, messages, redirect, $form) {
                     console.log(messages, redirect);
             }).on('formbuilder.error', function(ev, messages, $form) {
                     console.log(messages);
             }).on('formbuilder.error-field', function(ev, data, $form) {
                     console.log(data.field, data.messages);
             });

             */

            //add multi uploads
            this.$container.find('.formbuilder-html5File').each(function() {

                var $el = $(this),
                    $form = $el.closest('form'),
                    $submitButton = $form.find('input[type="submit"]'),
                    formConfig = $form.find('input[type="hidden"][name="_formConfig"]').val(),
                    $template = $el.find('.formbuilder-template:first'),
                    $element = $el.find('.formbuilder-content:first'),
                    messages = $template.find('input[name="js-messages"]').val();

                messages = jQuery.parseJSON( messages );

                if( jQuery().fineUploader !== undefined ) {

                    $el.fineUploader({
                        debug: false,
                        template: $template,
                        element: $element,
                        messages: messages.core,

                        text: {
                            formatProgress: messages.text.formatProgress,
                            failUpload: messages.text.failUpload,
                            waitingForResponse: messages.text.waitingForResponse,
                            paused: messages.text.paused
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
                                _formConfig: formConfig,
                                _fieldName: $element.data('field-name')
                            }
                        },

                        deleteFile: {
                            confirmMessage: messages.delete.confirmMessage,
                            deletingStatusText: messages.delete.deletingStatusText,
                            deletingFailedText: messages.delete.deletingFailedText,

                            enabled: true,
                            endpoint: _.getAjaxFileUrl('file_delete'),
                            params: {
                                _formConfig: formConfig,
                                _fieldName: $element.data('field-name')
                            }
                        },

                        validation: {
                            sizeLimit: $element.data('size-limit'),
                            allowedExtensions: $element.data('allowed-extensions').split(',')
                        },

                        callbacks: {

                            onUpload : function() {
                                $submitButton.attr('disabled', 'disabled');
                            },
                            onComplete : function() {
                                $submitButton.attr('disabled', false);
                            }
                        }

                    });
                }

            });

            this.$container.on('submit', function(ev) {

                if(_.ajaxUrls.length === 0) {
                    alert('formbuilder ajax url structure missing.');
                }

                var $form = $(this),
                    $btns = $form.find('.btn'),
                    $fbHtmlFile = $form.find('.formbuilder-html5File');

                ev.preventDefault();

                $btns.attr('disabled', 'disabled');

                if($fbHtmlFile.length > 0) {
                    $form.find('.qq-upload-delete').hide();
                }

                $.ajax({
                    type: $form.attr('method'),
                    url: _.getAjaxFileUrl('form_parser'),
                    data: ($form.attr('method') === 'get') ? $form.serialize() : new FormData( $form[0] ),
                    processData: ($form.attr('method') === 'get'),
                    contentType: ($form.attr('method') === 'get') ? $form.attr('enctype') : false,
                    success: function (response) {

                        $btns.attr('disabled', false);

                        $form.find('.help-block.validation').remove();
                        $form.find('.form-group').removeClass('has-error');

                        if($fbHtmlFile.length > 0) {
                            $form.find('.qq-upload-delete').show();
                        }

                        if(response.success === false) {

                            if(response.validation_errors !== false) {

                                $.each(response.validation_errors, function(fieldId, messages) {

                                    var $fields = $form.find('*[name*="' +fieldId +'"]'),
                                        $field = $fields.first(),
                                        $formGroup = null,
                                        $spanEl = null;

                                    if($field.length > 0) {

                                        $formGroup = $field.closest('.form-group');

                                        $.each(messages, function(validationType, message) {

                                            $formGroup.addClass('has-error');
                                            $formGroup.find('span.help-block.validation').remove();

                                            //its a multiple field
                                            $spanEl = $('<span/>', {'class' : 'help-block validation', 'text' : message});

                                            if($fields.length > 1 ) {
                                                $field.closest('label').before( $spanEl );
                                            } else {
                                                $field.before( $spanEl );
                                            }

                                        });

                                        $form.trigger('formbuilder.error-field', [{ 'field': $field, 'messages' : messages }, $form]);
                                    }

                                });

                            } else {
                                $form.trigger('formbuilder.error', [response.messages, $form]);
                            }

                        } else {

                            $form.trigger('formbuilder.success', [response.messages, response.redirect, $form]);
                            $form.find('input[type=text], textarea').val('');

                            if($fbHtmlFile.length > 0) {
                                $fbHtmlFile.fineUploader('reset');
                            }

                            if(typeof grecaptcha === 'object' && $form.find('.g-recaptcha:first').length > 0) {
                                grecaptcha.reset();
                            }
                        }
                    }
                });
            });
        },

        setAjaxFileStructureUrls: function() {

            var $form = this.$container.first();

            if($form.length === 0 || this.ajaxUrls.length > 0) {
                return;
            }

            $.ajax({
                type: 'post',
                url: $form.data('ajax-structure-url'),
                success: function (response) {
                    this.ajaxUrls = response;
                }.bind(this)
            });
        },

        getAjaxFileUrl: function(section) {
            return this.ajaxUrls[section];
        }
    };

    // API
    return {
        init: self.init
    };

})();

$(function() {
    'use strict';
    formBuilder.init();
});