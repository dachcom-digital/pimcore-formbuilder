/*

        __           __                                ___       _ __        __
   ____/ /___ ______/ /_  _________  ____ ___     ____/ (_)___ _(_) /_____ _/ /
  / __  / __ `/ ___/ __ \/ ___/ __ \/ __ `__ \   / __  / / __ `/ / __/ __ `/ /
 / /_/ / /_/ / /__/ / / / /__/ /_/ / / / / / /  / /_/ / / /_/ / / /_/ /_/ / /
 \__,_/\__,_/\___/_/ /_/\___/\____/_/ /_/ /_/   \__,_/_/\__, /_/\__/\__,_/_/
                                                       /____/

 copyright @ 2016, dachcom digital
 don't be a dick. don't copy.

*/
var Formbuilder = (function () {

    var self = {

        isBusy : false,

        config: {

            debug: false,
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

                ev.preventDefault();

                var $form = $(this),
                    $btns = $form.find('.btn');


                $btns.attr('disabled', 'disabled');

                $.ajax({
                    type: "POST",
                    url: "/plugin/Formbuilder/ajax/parse",
                    data: $form.serialize(),
                    success: function (response) {

                        $btns.attr('disabled', false);

                        $form.find('.help-block').remove();
                        $form.find('.form-group').removeClass('has-error');

                        if(response.success == false ) {

                            $.each( response.validationData, function( fieldId, messages) {

                                var $fields = $form.find('.element-' +fieldId),
                                    $field = $fields.first();

                                if( $field.length > 0) {

                                    var $formGroup = $field.closest('.form-group');

                                    $.each( messages, function( validationType, message) {

                                        $formGroup.addClass('has-error');
                                        $formGroup.find('span.help-block').remove();

                                        //its a multiple field
                                        var $spanEl = $('<span/>', {'class' : 'help-block', 'text' : message});

                                        if( $fields.length > 1 ) {
                                            $field.closest('label').before( $spanEl );
                                        } else {
                                            $field.before( $spanEl );
                                        }

                                        var name = $field.attr('name');

                                    });

                                    $form.trigger('formbuilder.error', [{'field': $field, 'messages' : messages}, $form])

                                }

                            });

                        } else {

                            $form.trigger('formbuilder.success', [response.message, $form])
                            $form.find("input[type=text], textarea").val("");

                            if( typeof grecaptcha == 'object') {
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

    }

})();

$(document).ready(Formbuilder.init.bind({debug: false, settings : null }));