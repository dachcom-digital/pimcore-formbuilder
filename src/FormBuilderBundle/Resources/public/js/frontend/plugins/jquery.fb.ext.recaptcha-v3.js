/*
 *  Project: PIMCORE FormBuilder
 *  Extension: reCAPTCHA V3 Injector
 *  Since: 3.2.0
 *  Author: DACHCOM.DIGITAL
 *  License: GPLv3

*/
;(function ($, window, document) {
    'use strict';
    var clName = 'ReCaptchaV3';

    function ReCaptchaV3(form, options) {
        this.siteKey = null;
        this.token = null;
        this.action = 'homepage';
        this.reCaptchaFieldClass = 'input.re-captcha-v3';
        this.$reCaptchaField = null;
        this.$form = $(form);
        this.options = $.extend({}, $.fn.formBuilderReCaptchaV3.defaults, options);
        this.init();
    }

    $.extend(ReCaptchaV3.prototype, {

        init: function () {

            this.$reCaptchaField = this.$form.find(this.reCaptchaFieldClass);

            if (this.$reCaptchaField.length === 0) {
                return;
            }

            if (this.$reCaptchaField.length > 1) {
                alert('Form has invalid amount of reCAPTCHA fields. There should be only one dedicated captcha field!');
                return;
            }

            this.disableFormSubmission();

            $('html').addClass('form-builder-rec3-available');

            this.siteKey = this.$reCaptchaField.data('site-key');
            this.action = this.$reCaptchaField.data('action-name');

            this.$form.on('formbuilder.success', this.onReset.bind(this));
            this.$form.on('formbuilder.fail', this.onReset.bind(this));

            this.bindDependency();

        },

        bindDependency: function () {

            if (typeof window.grecaptcha !== 'undefined') {
                grecaptcha.ready(this.injectTokenToForm.bind(this));
                return;
            }

            $.getScript('https://www.google.com/recaptcha/api.js?render=' + this.siteKey, function () {
                grecaptcha.ready(this.injectTokenToForm.bind(this));
            }.bind(this));
        },

        injectTokenToForm: function () {
            try {
                grecaptcha.execute(this.siteKey, {action: this.action}).then(this.onTokenGenerated.bind(this), function () {
                    this.$form.trigger('formbuilder.fatal-captcha', [null]);
                }.bind(this));
            } catch (error) {
                this.$form.trigger('formbuilder.fatal-captcha', [error]);
            }
        },

        /**
         * @param tokenGoogleRecaptchaV3
         */
        onTokenGenerated: function (tokenGoogleRecaptchaV3) {

            this.token = tokenGoogleRecaptchaV3;
            this.$reCaptchaField.val(tokenGoogleRecaptchaV3);

            this.enableFormSubmission();
        },

        onReset: function () {

            if (this.token === null) {
                return;
            }

            if (typeof window.grecaptcha === 'undefined') {
                return;
            }

            if (this.$reCaptchaField.length === 0) {
                return;
            }

            this.disableFormSubmission();
            this.injectTokenToForm();

        },

        disableFormSubmission: function () {

            if (this.options.disableFormWhileLoading !== true) {
                return;
            }

            this.$form.find('[type="submit"]').attr('disabled', 'disabled');
        },

        enableFormSubmission: function () {

            if (this.options.disableFormWhileLoading !== true) {
                return;
            }

            this.$form.find('[type="submit"]').attr('disabled', false);
        }
    });

    $.fn.formBuilderReCaptchaV3 = function (options) {
        this.each(function () {
            if (!$.data(this, 'fb-' + clName)) {
                $.data(this, 'fb-' + clName, new ReCaptchaV3(this, options));
            }
        });
        return this;
    };

    $.fn.formBuilderReCaptchaV3.defaults = {
        disableFormWhileLoading: true
    };

})(jQuery, window, document);
