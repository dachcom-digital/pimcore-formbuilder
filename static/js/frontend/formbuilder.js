var formBuilder = (function () {

    var self = {

        config: {
            settings : {}
        },

        init: function (options) {

            jQuery.extend(self.config, options);
            self.startSystem();

        },

        startSystem: function () {

            this.loadForms();

        },

        loadForms: function() {

            /*

            // Use those Events in your Project!
            $('form.ajax-form').on('formbuilder.success', function(ev, messages, $form) {
                console.log(messages);
            });

            $('form.ajax-form').on('formbuilder.error', function(ev, messages, $form) {
                console.log(messages);
            });

            */

            $('form.formbuilder.ajax-form').on('submit', function( ev ) {

                var $form = $(this),
                    $btns = $form.find('.btn');

                ev.preventDefault();

                $btns.attr('disabled', 'disabled');

                $.ajax({
                    type: 'POST',
                    url: '/plugin/Formbuilder/ajax/parse',
                    data: $form.serialize(),
                    success: function (response) {

                        $btns.attr('disabled', false);

                        $form.find('.help-block').remove();
                        $form.find('.form-group').removeClass('has-error');

                        if(response.success === false ) {

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

                                    $form.trigger('formbuilder.error', [{'field': $field, 'messages' : messages}, $form])

                                }

                            });

                        } else {

                            $form.trigger('formbuilder.success', [response.message, $form])
                            $form.find('input[type=text], textarea').val('');

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

$(document).ready(
    formBuilder.init.bind({settings : null})
);