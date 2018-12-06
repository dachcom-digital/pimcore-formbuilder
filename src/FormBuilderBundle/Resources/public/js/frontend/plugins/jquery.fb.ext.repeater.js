/*
 *  Project: PIMCORE FormBuilder
 *  Extension: Container - Repeater
 *  Since: 2.6.0
 *  Author: DACHCOM.DIGITAL
 *  License: GPLv3
 *
*/
;(function ($, window, document) {
    'use strict';
    var clName = 'Repeater';

    function Repeater(form, options) {
        this.containerClass = '.formbuilder-container.formbuilder-container-repeater';
        this.containerBlockClass = '.formbuilder-container-block';
        this.$form = $(form);
        this.options = $.extend({}, $.fn.formBuilderRepeater.defaults, options);
        this.$repeaterContainer = null;
        this.init();
    }

    $.extend(Repeater.prototype, {

        /**
         * Add Container Events and populate available containers with controls
         */
        init: function () {
            this.$repeaterContainer = $(this.containerClass);
            this.$repeaterContainer.each(this.setupContainer.bind(this));
            this.$repeaterContainer.on('click', '.add-block', this.onAdd.bind(this));
            this.$repeaterContainer.on('click', '.remove-block', this.onRemove.bind(this));
        },

        /**
         * @param index
         * @param container
         */
        setupContainer: function (index, container) {
            var $container = $(container),
                $blocks = $container.find(this.containerBlockClass);

            $container.data('index', $blocks.length);

            this.addCreateBlockButton(container);
            $blocks.each(this.addRemoveBlockButton.bind(this, container));
        },

        /**
         * @param container
         */
        addCreateBlockButton: function (container) {
            var $container = $(container),
                $element = $('<a/>', {
                    'href': '#',
                    'class': this.options.classes.add + ' add-block',
                    'text': this.options.labels.addNewSection
                });

            $container.append($element);
            this.verifyButtonStates($container);
        },

        /**
         * @param container
         * @param index
         * @param block
         */
        addRemoveBlockButton: function (container, index, block) {
            var $container = $(container),
                $block = $(block),
                $element = $('<a/>', {
                    'href': '#',
                    'class': this.options.classes.remove + ' remove-block',
                    'text': this.options.labels.removeSection
                });

            if ($container.data('repeater-min') && (this.getContainerBlockAmount($container)) <= $container.data('repeater-min')) {
                return;
            }

            $block.append($element);
        },

        /**
         * @param ev
         */
        onAdd: function (ev) {
            var $button = $(ev.target),
                container = ev.delegateTarget,
                $container = $(container),
                newFormPrototype = $container.data('prototype'),
                index = $container.data('index'),
                newIndex = index + 1,
                newForm = newFormPrototype.replace(/__name__/g, index).replace(/__label__/g, (this.getContainerBlockAmount($container) + 1)),
                $newForm, cb;

            ev.preventDefault();

            $container.data('index', newIndex);

            cb = function ($newForm) {
                this.addRemoveBlockButton(container, index, $newForm);
                this.verifyButtonStates($container);
            }.bind(this);

            if (typeof this.options.onAdd === 'function') {
                this.options.onAdd.call(container, newForm, cb.bind(this));
            } else {
                $newForm = $(newForm);
                $newForm.insertBefore($button);
                cb($newForm);
            }
        },

        /**
         * @param ev
         */
        onRemove: function (ev) {
            var cb, $containerBlock = $(ev.target).closest(this.containerBlockClass),
                $container = $containerBlock.closest(this.containerClass);

            ev.preventDefault();

            cb = function () {
                this.reRenderBlockLabels($container);
                this.verifyButtonStates($container);
            }.bind(this);

            if (typeof this.options.onRemove === 'function') {
                this.options.onRemove.call($containerBlock[0], cb.bind(this));
            } else {
                $containerBlock.slideUp(250, function () {
                    $(this).remove();
                    cb();
                });
            }
        },

        /**
         * @param $container
         */
        verifyButtonStates: function ($container) {
            var $addButton = $container.find('.add-block');
            $addButton.css('display', this.canAddNewBlock($container) ? 'inline-block' : 'none');
        },

        /**
         * @param $container
         */
        reRenderBlockLabels: function ($container) {
            var $blocks = $container.find(this.containerBlockClass), counter = 1;
            $blocks.each(function (index, block) {
                var labelText = '', $block = $(block), $label = $block.find('[data-label-template]:first');
                if ($label.length === 1) {
                    labelText = $label.data('label-template');
                    $label.text(labelText.replace(/__label_index__/, counter));
                    counter++;
                }
            });
        },

        /**
         * @param $container
         * @returns {boolean}
         */
        canAddNewBlock: function ($container) {
            if (!$container.data('repeater-max')) {
                return true;
            }

            return this.getContainerBlockAmount($container) < $container.data('repeater-max');
        },

        /**
         * @param $container
         * @returns []
         */
        getContainerBlockAmount: function ($container) {
            return $container.find(this.containerBlockClass).length;
        }

    });

    $.fn.formBuilderRepeater = function (options) {
        this.each(function () {
            if (!$.data(this, 'fb-' + clName)) {
                $.data(this, 'fb-' + clName, new Repeater(this, options));
            }
        });
        return this;
    };

    $.fn.formBuilderRepeater.defaults = {
        onAdd: null,
        onRemove: null,
        classes: {
            add: 'btn btn-info',
            remove: 'btn btn-danger'
        },
        labels: {
            addNewSection: 'Add New',
            removeSection: 'Remove',
        }
    };

})(jQuery, window, document);