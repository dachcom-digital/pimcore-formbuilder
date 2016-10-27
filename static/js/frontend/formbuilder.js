/*
        __           __                                ___       _ __        __
   ____/ /___ ______/ /_  _________  ____ ___     ____/ (_)___ _(_) /_____ _/ /
  / __  / __ `/ ___/ __ \/ ___/ __ \/ __ `__ \   / __  / / __ `/ / __/ __ `/ /
 / /_/ / /_/ / /__/ / / / /__/ /_/ / / / / / /  / /_/ / / /_/ / / /_/ /_/ / /
 \__,_/\__,_/\___/_/ /_/\___/\____/_/ /_/ /_/   \__,_/_/\__, /_/\__/\__,_/_/
                                                       /____/
 copyright @ 2016, dachcom digital

 */
var formBuilder = (function () {

    'use strict';

    var self = {

        init: function () {

            self.startSystem();

        },

        startSystem: function () {

            this.loadForms();

        },

        loadForms: function() {

            /*

             // Use those Events in your Project!

             $('form.ajax-form').on('formbuilder.success', function(ev, message, redirect, $form) {
             console.log(messages);
             }).on('formbuilder.error', function(ev, message, $form) {
             console.log(messages);
             }).on('formbuilder.error-field', function(ev, data, $form) {
             console.log(messages);
             });

             */

            //add multiuploads
            $('form.formbuilder.ajax-form .formbuilder-html5File').each(function() {

                var $el = $(this),
                    $form = $el.closest('form'),
                    $submitButton = $form.find('input[type="submit"]'),
                    formId = $form.find('input[type="hidden"][name="_formId"]').val(),
                    $template = $el.find('.formbuilder-template:first'),
                    $element = $el.find('.formbuilder-content:first'),
                    messages = $template.find('input[name="js-messages"]').val();

                messages = jQuery.parseJSON( messages );

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
                            endpoint: '/plugin/Formbuilder/ajax/chunk-done'
                        }
                    },

                    request: {
                        endpoint: '/plugin/Formbuilder/ajax/add-from-upload',
                        params: {
                            _formId: formId
                        }
                    },

                    deleteFile: {
                        confirmMessage: messages.delete.confirmMessage,
                        deletingStatusText: messages.delete.deletingStatusText,
                        deletingFailedText: messages.delete.deletingFailedText,

                        enabled: true,
                        endpoint: '/plugin/Formbuilder/ajax/delete-from-upload',
                        params: {
                            _formId: formId
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

            });

            $('form.formbuilder.ajax-form').on('submit', function( ev ) {

                var $form = $(this),
                    $btns = $form.find('.btn'),
                    formData = new FormData( $form[0] ); //$form.serialize();

                ev.preventDefault();

                $btns.attr('disabled', 'disabled');

                $form.find('.qq-upload-delete').hide();

                $.ajax({
                    type: 'POST',
                    url: '/plugin/Formbuilder/ajax/parse',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function ( response ) {

                        $btns.attr('disabled', false);

                        $form.find('.help-block').remove();
                        $form.find('.form-group').removeClass('has-error');

                        if(response.success === false ) {

                            $form.find('.qq-upload-delete').show();

                            if( response.validationData !== false ) {

                                $.each( response.validationData, function( fieldId, messages) {

                                    var $fields = $form.find('.element-' +fieldId),
                                        $field = $fields.first(),
                                        $formGroup = null,
                                        $spanEl = null;

                                    if( $field.length > 0) {

                                        $formGroup = $field.closest('.form-group');

                                        $.each( messages, function( validationType, message) {

                                            $formGroup.addClass('has-error');
                                            $formGroup.find('span.help-block').remove();

                                            //its a multiple field
                                            $spanEl = $('<span/>', {'class' : 'help-block', 'text' : message});

                                            if( $fields.length > 1 ) {
                                                $field.closest('label').before( $spanEl );
                                            } else {
                                                $field.before( $spanEl );
                                            }

                                        });

                                        $form.trigger('formbuilder.error-field', [ { 'field': $field, 'messages' : messages }, $form ]);

                                    }

                                });

                            } else {

                                $form.trigger('formbuilder.error', [ response.message, $form ]);
                            }

                        } else {

                            $form.trigger('formbuilder.success', [ response.message, response.redirect, $form ]);
                            $form.find('input[type=text], textarea').val('');

                            $('form').find('.formbuilder-html5File').fineUploader('reset');

                            if( typeof grecaptcha === 'object' && $form.find('.g-recaptcha:first').length > 0) {
                                grecaptcha.reset();
                            }

                        }

                    }

                });

            });

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