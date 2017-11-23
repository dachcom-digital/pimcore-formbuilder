;(function ($, window, document) {
    'use strict';
    var clName = 'ConditionalLogic';

    function QualifiersApplier(internal, options) {
        this.onCheck = options && typeof options.onCheck === 'function' ? options.onCheck : internal.check;
    }

    function ActionApplier(internal, options) {
        this.onEnable = options && typeof options.onEnable === 'function' ? options.onEnable : internal.enable;
        this.onDisable = options && typeof options.onDisable === 'function' ? options.onDisable : internal.disable;
    }

    function ElementTransformer(options, formTemplate) {
        this.formTemplate = formTemplate;
        this.userMethods = options;
        this.themeTransform = {
            'bootstrap3': {
                show: function ($el) {
                    $el.show();
                    $el.prev('label').show().parent('label').show();
                },
                hide: function ($el) {
                    $el.val('');
                    $el.hide();
                    $el.prev('.help-block').remove();
                    $el.prev('label').hide().parent('label').hide();
                },
                addRequiredState: function ($el) {
                    $el.attr('required', 'required');
                    $el.prev('label').addClass('required');
                },
                removeRequiredState: function ($el) {
                    $el.removeAttr('required');
                    $el.prev('.help-block').remove();
                    $el.prev('label').removeClass('required');
                    $el.parent().removeClass('has-error');
                }
            },
            'bootstrap4': {}
        };

        this.transform = function () {

            var args = Array.prototype.slice.call(arguments),
                action = args.shift();

            if (typeof this.userMethods[action] === 'function') {
                return this.userMethods[action].apply(null, args);
            }

            switch (this.formTemplate) {
                case 'bootstrap_3_layout':
                case 'bootstrap_3_horizontal_layout':
                    return this.themeTransform.bootstrap3[action].apply(null, args);
                    break;
                case 'bootstrap_4_layout':
                case 'bootstrap_5_horizontal_layout':
                    return this.themeTransform.bootstrap4[action].apply(null, args);
                    break;
                default:
                    console.warn('unknown element transformer action found!', action);
                    break;
            }
        }
    }

    function ConditionalLogic(form, options) {
        this.$form = $(form);
        this.formTemplate = this.$form.data('template');
        this.options = $.extend({}, $.fn.formBuilderConditionalLogic.defaults, options);
        this.logic = {};

        this.actions = {};
        this.conditions = {};
        this.elementTransformer = new ElementTransformer(this.options.elementTransformer, this.formTemplate);

        this.setupConditionProcessor();
        this.setupActionProcessor();
        this.init();

    }

    $.extend(ConditionalLogic.prototype, {

        setupConditionProcessor: function () {

            var elementValue = new QualifiersApplier({
                check: function (condition) {
                    var qualifiers = {};
                    switch (condition.comparator) {
                        case 'is_greater':
                            qualifiers['greater'] = function (val) {
                                return parseInt(val) > parseInt(condition.value);
                            }
                            break;
                        case 'is_less':
                            qualifiers['less'] = function (val) {
                                return parseInt(val) < parseInt(condition.value);
                            }
                            break;
                        case 'is_value':
                            qualifiers['values'] = [condition.value]
                            break;
                        case 'is_selected':
                            qualifiers['checked'] = true
                            break;
                    }

                    return qualifiers;
                }
            }, this.options.conditions.elementValue);

            this.conditions = {
                'elementValue': elementValue
            };

        },

        setupActionProcessor: function () {

            var _ = this;

            var toggleElement = new ActionApplier({
                enable: function (action, ev, $el) {
                    if (action.state === 'hide') {
                        _.elementTransformer.transform('hide', $el);
                    } else {
                        _.elementTransformer.transform('show', $el);
                    }
                },
                disable: function (action, ev, $el) {
                    if (action.state === 'show') {
                        _.elementTransformer.transform('hide', $el);
                    } else {
                        _.elementTransformer.transform('show', $el);
                    }
                }
            }, this.options.actions.toggleElement);

            var processor = {
                enable: function (action, ev, $el) {
                },
                disable: function (action, ev, $el) {
                }
            };

            var changeValue = new ActionApplier({
                enable: function (action, ev, $el) {
                },
                disable: function (action, ev, $el) {
                }
            }, this.options.actions.changeValue);

            var triggerEvent = new ActionApplier({
                enable: function (action, ev, $el) {
                    $el.trigger(action.event + '.enable');
                },
                disable: function (action, ev, $el) {
                    $el.trigger(action.event + '.disable');
                }
            }, this.options.actions.triggerEvent);

            var toggleClass = new ActionApplier({
                enable: function (action, ev, $el) {
                },
                disable: function (action, ev, $el) {
                }
            }, this.options.actions.toggleClass);

            var constraintsAdd = new ActionApplier({
                enable: function (action, ev, $el) {
                    if (!$el.data('cl-had-add-required-state')) {
                        $el.data('cl-had-add-required-state', $el.attr('required') !== undefined);
                    }

                    _.elementTransformer.transform('addRequiredState', $el);

                },
                disable: function (action, ev, $el) {
                    if (!$el.data('cl-had-add-required-state')) {
                        $el.data('cl-had-add-required-state', $el.attr('required') !== undefined);
                    }
                    if ($el.data('cl-had-add-required-state') === false) {
                        _.elementTransformer.transform('removeRequiredState', $el);
                    }
                }
            }, this.options.actions.constraintsAdd);

            var constraintsRemove = new ActionApplier({
                enable: function (action, ev, $el) {
                    if (!$el.data('cl-had-remove-required-state')) {
                        $el.data('cl-had-remove-required-state', $el.attr('required') !== undefined);
                    }
                    if (action.removeAllValidations === true) {
                        _.elementTransformer.transform('removeRequiredState', $el);
                    }
                },
                disable: function (action, ev, $el) {
                    if (!$el.data('cl-had-remove-required-state')) {
                        $el.data('cl-had-remove-required-state', $el.attr('required') !== undefined);
                    }
                    if (action.removeAllValidations === true && $el.data('cl-had-remove-required-state') === true) {
                        _.elementTransformer.transform('addRequiredState', $el);
                    }
                }
            }, this.options.actions.constraintsRemove);

            this.actions = {
                'toggleElement': toggleElement,
                'changeValue': changeValue,
                'triggerEvent': triggerEvent,
                'toggleClass': toggleClass,
                'constraintsAdd': constraintsAdd,
                'constraintsRemove': constraintsRemove,
            };
        },

        init: function () {

            var $clField = this.$form.find('input[name*="formCl"]'),
                value = null, data = null;

            if ($clField.length === 0) {
                return;
            }

            value = $clField.val();
            if (!value) {
                return;
            }

            try {
                this.logic = $.parseJSON(value);
            } catch (e) {
                console.warn('error while parsing conditional logic data. error was: ' + e);
                return;
            }

            this.parseConditions();
        },

        /**
         * add logic to each form.
         */
        parseConditions: function () {
            var _ = this;
            $.each(_.logic, function (blockId, block) {
                var dependingStructure = [];

                //invalid conditional logic stack.
                if (block === null) {
                    return true;
                }

                var actions = block.action;
                $.each(actions, function (i, action) {
                    var actionFields = action.fields;
                    var dependFields = [];
                    $.each(actionFields, function (i, field) {
                        dependFields.push(field);
                    }.bind(this))
                    dependingStructure.push({'action': action, 'condition': block.condition, 'fields': dependFields});
                }.bind(this))

                //create dependency structure for each group.
                var formSelector = 'form[name="' + _.$form.prop('name') + '"]';
                $.each(dependingStructure, function (i, dependency) {
                    var formDependingSelector = [];
                    $.each(dependency.fields, function (fieldIndex, fieldName) {
                        formDependingSelector.push('*[name*="' + fieldName + '"]');
                    });

                    if (dependency.condition && dependency.condition.length > 0) {
                        var conditionSelector = _.generateQualifiersSelector(dependency.condition, formSelector);
                    }

                    var actionOptions = _.generateActionOptions(dependency.action);

                    //no valid action found - skip field!
                    if (actionOptions === false) {
                        return true;
                    }

                    var $conditionField = _.$form.find(formDependingSelector.join(',')).dependsOn(conditionSelector, actionOptions);
                    //console.log('add condition to', formDependingSelector, 'depends on: ', conditionSelector, 'actionOptions:' , actionOptions);

                });

            }.bind(this));
        },

        /**
         * generate qualifiers depending on condition data.
         *
         * @param conditions
         * @param formSelector
         * @returns {{}}
         */
        generateQualifiersSelector: function (conditions, formSelector) {
            var _ = this,
                conditionSelector = {};
            $.each(conditions, function (conditionId, condition) {
                var conditionType = condition.type,
                    qualifiers = {};

                switch (conditionType) {
                    case 'elementValue':
                        qualifiers = _.conditions.elementValue.onCheck(condition);
                        break;
                }

                $.each(condition.fields, function (fieldIndex, field) {
                    var fieldSelector = formSelector + ' *[name*="' + field + '"]',
                        el = $(fieldSelector);

                    //it's probably a checkbox/radio. get the one with valid value - if given!
                    if (el.length > 1 && condition.type === 'elementValue' && condition.value !== '') {
                        fieldSelector += '[value="' + condition.value + '"]';
                    }

                    conditionSelector[fieldSelector] = qualifiers
                });
            });

            return conditionSelector;
        },

        /**
         *
         * @param action
         * @returns {boolean}
         */
        generateActionOptions: function (action) {
            var _ = this,
                options = false,
                actionType = action.type;
            switch (actionType) {
                case 'toggleElement':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.toggleElement.onEnable.bind(null, action),
                        onDisable: _.actions.toggleElement.onDisable.bind(null, action)
                    }
                    break;
                case 'changeValue':
                    options = {
                        hide: false,
                        disable: false,
                        valueOnEnable: action.value,
                        onEnable: _.actions.changeValue.onEnable.bind(null, action),
                        onDisable: _.actions.changeValue.onDisable.bind(null, action)
                    }
                    break;
                case 'triggerEvent':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.triggerEvent.onEnable.bind(null, action),
                        onDisable: _.actions.triggerEvent.onDisable.bind(null, action)
                    }
                    break;
                case 'toggleClass':
                    options = {
                        hide: false,
                        disable: false,
                        toggleClass: action.class,
                        onEnable: _.actions.toggleClass.onEnable.bind(null, action),
                        onDisable: _.actions.toggleClass.onDisable.bind(null, action)
                    }
                    break;
                case 'constraintsAdd':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.constraintsAdd.onEnable.bind(null, action),
                        onDisable: _.actions.constraintsAdd.onDisable.bind(null, action)
                    }
                    break;
                case 'constraintsRemove':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.constraintsRemove.onEnable.bind(null, action),
                        onDisable: _.actions.constraintsRemove.onDisable.bind(null, action)
                    }
                    break;
                default:
                    options = false
            }
            return options;
        }

    });

    $.fn.formBuilderConditionalLogic = function (options) {
        this.each(function () {
            if (!$.data(this, 'fb-' + clName)) {
                $.data(this, 'fb-' + clName, new ConditionalLogic(this, options));
            }
        });
        return this;
    };

    $.fn.formBuilderConditionalLogic.defaults = {
        conditions: {},
        actions: {
            toggleElement: {}
        },
        elementTransformer: {}
    };

})(jQuery, window, document);