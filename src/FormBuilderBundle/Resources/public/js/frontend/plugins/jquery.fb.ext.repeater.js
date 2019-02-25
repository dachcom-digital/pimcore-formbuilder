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
            this.$repeaterContainer = this.$form.find(this.containerClass);
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
            this.verifyButtonStates(container);
            $blocks.each(this.addRemoveBlockButton.bind(this, container));

            $container.on('formbuilder.repeater.container.update', function (ev) {
                this.reRenderBlockLabels(ev.target);
            }.bind(this));
        },

        /**
         * @param container
         */
        addCreateBlockButton: function (container) {
            var $container = $(container),
                $element = this.renderCreateBlockElement(container);

            if (typeof this.options.allocateCreateBlockElement === 'function') {
                this.options.allocateCreateBlockElement.call(container, $element);
            } else {
                $container.append($element);
            }
        },

        /**
         * @param container
         * @returns {*}
         */
        renderCreateBlockElement: function (container) {
            var $element = null,
                classes = this.options.classes.add + ' add-block',
                text = $(container).data('label-add-block');

            if (typeof this.options.renderCreateBlockElement === 'function') {
                $element = this.options.renderCreateBlockElement.call(container, classes, text);
            } else {
                $element = $('<a/>', {
                    'href': '#',
                    'class': classes,
                    'text': text
                });
            }

            if (!$element.hasClass('add-block')) {
                console.error('Formbuilder Repeater: Button requires a .add-block class to work properly.');
            }

            return $element;
        },

        /**
         * @param container
         * @param index
         * @param block
         */
        addRemoveBlockButton: function (container, index, block) {
            var $container = $(container),
                $block = $(block),
                $element = this.renderRemoveBlockElement(container, block);

            if ($container.data('repeater-min') && (this.getContainerBlockAmount(container)) <= $container.data('repeater-min')) {
                return;
            }

            if (typeof this.options.allocateRemoveBlockElement === 'function') {
                this.options.allocateRemoveBlockElement.call(block, $element);
            } else {
                $block.append($element);
            }
        },

        /**
         * @param container
         * @param block
         * @returns {*}
         */
        renderRemoveBlockElement: function (container, block) {
            var $element = null,
                classes = this.options.classes.remove + ' remove-block',
                text = $(container).data('label-remove-block');

            if (typeof this.options.renderRemoveBlockElement === 'function') {
                $element = this.options.renderRemoveBlockElement.call(block, classes, text);
            } else {
                $element = $('<a/>', {
                    'href': '#',
                    'class': classes,
                    'text': text
                });
            }

            if (!$element.hasClass('remove-block')) {
                console.error('Formbuilder Repeater: Button requires a .remove-block class to work properly.');
            }

            return $element;
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
                newForm = newFormPrototype.replace(/__name__/g, index).replace(/__label__/g, (this.getContainerBlockAmount(container) + 1)),
                $newForm, cb;

            ev.preventDefault();

            $container.data('index', newIndex);

            cb = function ($newForm) {
                this.reRenderBlockLabels(container);
                this.addRemoveBlockButton(container, index, $newForm);
                this.verifyButtonStates(container);
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
                this.reRenderBlockLabels($container[0]);
                this.verifyButtonStates($container[0]);
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
         * @param container
         */
        verifyButtonStates: function (container) {
            var $container = $(container),
                $addButton = $container.find('.add-block');
            $addButton.css('display', this.canAddNewBlock(container) ? 'inline-block' : 'none');
        },

        /**
         * @param container
         */
        reRenderBlockLabels: function (container) {
            var $container = $(container),
                $blocks = $container.find(this.containerBlockClass), counter = 1;
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
         * @param container
         * @returns {boolean}
         */
        canAddNewBlock: function (container) {
            var $container = $(container);
            if (!$container.data('repeater-max')) {
                return true;
            }

            return this.getContainerBlockAmount(container) < $container.data('repeater-max');
        },

        /**
         * @param container
         * @returns {number}
         */
        getContainerBlockAmount: function (container) {
            var $container = $(container);
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
        renderCreateBlockElement: null,
        renderRemoveBlockElement: null,
        allocateCreateBlockElement: null,
        allocateRemoveBlockElement: null,
        classes: {
            add: 'btn btn-info',
            remove: 'btn btn-danger'
        }
    };

})(jQuery, window, document);