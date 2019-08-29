/*
 *  Project: PIMCORE FormBuilder
 *  Extension: Tracker (TagManager | matomo)
 *  Since: 2.7.1
 *  Author: DACHCOM.DIGITAL
 *  License: GPLv3
 *
*/
;(function ($, window, document) {
    'use strict';
    var clName = 'Tracker';

    function Tracker(form, options) {
        this.duplicateNameCounter = {};
        this.$form = $(form);
        this.options = $.extend({}, $.fn.formBuilderTracker.defaults, options);
        this.init();
    }

    $.extend(Tracker.prototype, {

        /**
         * Submit Data to Tracker only if form submission was successful
         */
        init: function () {
            this.$form.on('formbuilder.success', this.onSubmission.bind(this));
        },

        /**
         * @param ev
         * @param message
         * @param redirect
         * @param $form
         */
        onSubmission: function (ev, message, redirect, $form) {

            var data,
                formName = this.findFormName($form);

            this.duplicateNameCounter = {};

            data = {
                'event': 'form_builder_submission',
                'type': 'success',
                'form_name': formName,
                'values': this.findFormValues($form, formName)
            };

            if (typeof this.options.onBeforeSubmitDataToProvider === 'function') {
                data = this.options.onBeforeSubmitDataToProvider(data, formName, $form);
            }

            if (!this.isObject(data)) {
                console.error('invalid data for tracker provider', data);
                return;
            }

            if (this.options.provider === 'google') {
                this.submitToGoogle(data);
            } else if (this.options.provider === 'matomo') {
                this.submitToMatomo(data);
            }
        },

        submitToGoogle: function (data) {

            if (typeof window.dataLayer === 'object') {
                window.dataLayer.push(data);
            } else if (typeof window.gtag === 'function') {
                gtag('event', data.event, {
                    'event_category': data.type,
                    'event_label': data.form_name
                });
            } else if (typeof window.ga === 'function') {
                ga('send', 'event', data.event, data.type, data.form_name);
            }
        },

        submitToMatomo: function (data) {

            var stringValues = '';
            if (window.JSON.stringify) {
                stringValues = JSON.stringify(data.values);
            }

            if (typeof window._mtm === 'object') {
                // first, try matomo tag manager
                _mtm.push(data);
            } else if (typeof window._paq === 'object') {
                // second, try matomo event dispatcher
                _paq.push(['trackEvent', data.event, data.type, data.form_name, stringValues]);
            }
        },

        isObject: function (value) {
            return value && typeof value === 'object' && value.constructor === Object;
        },

        findFormName: function ($form) {
            return $form.attr('name');
        },

        findFormValues: function ($form, formName) {

            var selector = [],
                fieldData = {},
                $fields;

            if (this.options.trackDropDownSelection === true) {
                selector.push('select')
            }

            if (this.options.trackCheckboxSelection === true) {
                selector.push('input[type="checkbox"]:checked')
            }

            if (this.options.trackRadioSelection === true) {
                selector.push('input[type="radio"]:checked')
            }
            if (this.options.trackHiddenInputs === true) {
                selector.push('input[type="hidden"]')
            }

            $fields = $form.find(selector.join());

            if ($fields.length === 0) {
                return {};
            }

            $.each($fields, function (i, field) {

                var $field = $(field),
                    name = this.parseFieldName($field.attr('name'), formName),
                    value = $field.val();

                if (value === null || value === '') {
                    return;
                }

                if (name === null) {
                    return;
                }

                fieldData[name] = value;

            }.bind(this));

            return fieldData;
        },

        parseFieldName: function (fieldName, formName) {

            var suffix = '',
                invalidTest = new RegExp(this.options.invalidFieldNames.join('|'));

            if (typeof fieldName !== 'string') {
                return null;
            }

            fieldName = fieldName.replace(formName, '');
            fieldName = fieldName.replace('][', '_');
            fieldName = fieldName.replace('[', '');
            fieldName = fieldName.replace(']', '');

            if (fieldName.match(invalidTest)) {
                return null;
            }

            if (this.duplicateNameCounter.hasOwnProperty(fieldName)) {
                suffix = '_' + (this.duplicateNameCounter[fieldName] + 1);
                this.duplicateNameCounter[fieldName]++;
            } else {
                this.duplicateNameCounter[fieldName] = 1;
            }

            if (suffix === '' && fieldName.substr(fieldName.length - 1) === '_') {
                suffix = '_1';
            }

            fieldName = fieldName + suffix;
            fieldName = fieldName.replace('__', '_');

            return fieldName;
        }

    });

    $.fn.formBuilderTracker = function (options) {
        this.each(function () {
            if (!$.data(this, 'fb-' + clName)) {
                $.data(this, 'fb-' + clName, new Tracker(this, options));
            }
        });
        return this;
    };

    $.fn.formBuilderTracker.defaults = {
        onBeforeSubmitDataToProvider: null,
        provider: 'google',
        trackDropDownSelection: true,
        trackCheckboxSelection: true,
        trackRadioSelection: true,
        trackHiddenInputs: true,
        invalidFieldNames: ['_token', 'formCl']
    };

})(jQuery, window, document);