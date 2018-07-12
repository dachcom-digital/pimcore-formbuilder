/*
 *  Project: PIMCORE FormBuilder
 *  Extension: Conditional Logic
 *  Version: 2.2.1
 *  Author: DACHCOM.DIGITAL
 *  License: GPLv3
 *
*/
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
                show: function ($els, className) {
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().removeClass(className);
                    });
                },
                hide: function ($els, className) {
                    $els.val('').prop('selectedIndex', 0);
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().addClass(className);
                    });
                },
                addClass: function ($els, className) {
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().addClass(className);
                    });
                },
                removeClass: function ($els, className) {
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().removeClass(className);
                    });
                },
                enable: function ($els) {
                    $els.removeAttr('disabled');
                },
                disable: function ($els) {
                    $els.attr('disabled', 'disabled');
                },
                addRequiredState: function ($els) {
                    $els.attr('required', 'required');
                    $els.each(function () {
                        var $el = $(this);
                        //its a form-group field
                        if ($el.parent('label').length > 0) {
                            var $label = $el.parent('label');
                            $label.parentsUntil('.form-group').parent().find('.control-label').addClass('required');
                        } else {
                            $el.prev('label').addClass('required');
                        }
                    });
                },
                removeRequiredState: function ($els) {
                    $els.removeAttr('required');
                    $els.each(function () {
                        var $el = $(this);
                        //its a form-group field
                        if ($el.parent('label').length > 0) {
                            var $label = $el.parent('label');
                            $label.prev('.help-block').remove();
                            $label.parentsUntil('.form-group').parent().removeClass('has-error').find('.control-label').removeClass('required');
                        } else {
                            $el.prev('.help-block').remove();
                            $el.prev('label').removeClass('required');
                            $el.parent().removeClass('has-error');
                        }
                    });
                }
            },
            'bootstrap4': {
                show: function ($els, className) {
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().removeClass(className);
                    });
                },
                hide: function ($els, className) {
                    $els.val('').prop('selectedIndex', 0);
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().addClass(className);
                    });
                },
                addClass: function ($els, className) {
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().addClass(className);
                    });
                },
                removeClass: function ($els, className) {
                    $els.each(function () {
                        $(this).parentsUntil('*[class^=col-]').parent().removeClass(className);
                    });
                },
                enable: function ($els) {
                    $els.removeAttr('disabled');
                },
                disable: function ($els) {
                    $els.attr('disabled', 'disabled');
                },
                addRequiredState: function ($els) {
                    $els.attr('required', 'required');
                    $els.each(function () {
                        var $el = $(this);
                        $el.addClass('is-invalid');
                        // default
                        if ($el.prev('label').length > 0) {
                            $el.prev('label').addClass('required');
                            // custom control type
                        } else if ($el.next('label').length > 0) {
                            $el.closest('.form-group').find('.col-form-legend').addClass('required');
                        }
                    });
                },
                removeRequiredState: function ($els) {
                    $els.removeAttr('required');
                    $els.each(function () {
                        var $el = $(this);
                        $el.removeClass('is-invalid');
                        // default
                        if ($el.prev('label').length > 0) {
                            $el.next('.invalid-feedback').remove();
                            $el.prev('label').removeClass('required');
                            // custom control type
                        } else if ($el.next('label').length > 0) {
                            $el.closest('.form-group').find('.col-form-legend').addClass('required');
                            $el.parentsUntil('.form-group').parent().find('.invalid-feedback').remove();
                        }
                    });
                }
            }
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
                case 'bootstrap_4_horizontal_layout':
                    return this.themeTransform.bootstrap4[action].apply(null, args);
                    break;
                default:
                    console.warn('unknown element transformer action found.', action);
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

        this.$form.addClass('fb-cl-initialized');
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
                            qualifiers['values'] = [condition.value];
                            break;
                        case 'is_not_value':
                            qualifiers['not'] = [condition.value];
                            break;
                        case 'is_empty_value':
                            qualifiers['values'] = [undefined, ''];
                            break;
                        case 'contains':
                            qualifiers['contains'] = condition.value.split(',');
                            break;
                        case 'is_checked':
                            qualifiers['not'] = [undefined];
                            qualifiers['checked'] = true;
                            break;
                        case 'is_not_checked':
                            qualifiers['checked'] = false;
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
                enable: function (action, actionId, ev, $el) {
                    if (action.state === 'hide') {
                        _.elementTransformer.transform('hide', $el, 'fb-cl-hide-element');
                    } else {
                        _.elementTransformer.transform('show', $el, 'fb-cl-hide-element');
                    }
                },
                disable: function (action, actionId, ev, $el) {
                    if (action.state === 'show') {
                        _.elementTransformer.transform('hide', $el, 'fb-cl-hide-element');
                    } else {
                        _.elementTransformer.transform('show', $el, 'fb-cl-hide-element');
                    }
                }
            }, this.options.actions.toggleElement);

            var changeValue = new ActionApplier({
                enable: function (action, actionId, ev, $el) {
                    $el.each(function () {
                        if ($(this).is('input[type="text"]') || $(this).is('input[type="number"]')) {
                            $el.val(action.value);
                        } else if ($(this).is('select') && $el.find('option[value="' + action.value + '"]').length > 0) {
                            $el.val(action.value);
                        }
                    });
                },
                disable: function (action, actionId, ev, $el) {
                    $el.each(function () {
                        if ($(this).is('input[type="text"]') || $(this).is('input[type="number"]')) {
                            $el.val('');
                        } else if ($(this).is('select')) {
                            $el.prop('selectedIndex', 0);
                        }
                    });
                }
            }, this.options.actions.changeValue);

            var triggerEvent = new ActionApplier({
                enable: function (action, actionId, ev, $el) {
                    $el.trigger(action.event + '.enable');
                },
                disable: function (action, actionId, ev, $el) {
                    $el.trigger(action.event + '.disable');
                }
            }, this.options.actions.triggerEvent);

            var toggleClass = new ActionApplier({
                enable: function (action, actionId, ev, $el) {
                    _.elementTransformer.transform('addClass', $el, action.class);
                },
                disable: function (action, actionId, ev, $el) {
                    _.elementTransformer.transform('removeClass', $el, action.class);
                }
            }, this.options.actions.toggleClass);

            var toggleAvailability = new ActionApplier({
                enable: function (action, actionId, ev, $el) {
                    if (action.state === 'disable') {
                        _.elementTransformer.transform('disable', $el);
                    } else {
                        _.elementTransformer.transform('enable', $el);
                    }
                },
                disable: function (action, actionId, ev, $el) {
                    if (action.state === 'enable') {
                        _.elementTransformer.transform('disable', $el);
                    } else {
                        _.elementTransformer.transform('enable', $el);
                    }
                }
            }, this.options.actions.toggleAvailability);

            var constraintsAdd = new ActionApplier({
                enable: function (action, actionId, ev, $el) {
                    // initial constraints
                    var hic = $el.data('fb.cl.has-initial-required-constraint'),
                        ic = $el.data('fb.cl.initial-constraints');

                    if ($.isArray(action.validation) && $.inArray('not_blank', action.validation) !== -1) {
                        _.elementTransformer.transform('addRequiredState', $el);
                    }
                },
                disable: function (action, actionId, ev, $el) {
                    // initial constraints
                    var hic = $el.data('fb.cl.has-initial-required-constraint'),
                        ic = $el.data('fb.cl.initial-constraints');

                    if (hic === true) {
                        _.elementTransformer.transform('addRequiredState', $el);
                    } else if ($.isArray(action.validation) && $.inArray('not_blank', action.validation) !== -1) {
                        _.elementTransformer.transform('removeRequiredState', $el);
                    }
                }
            }, this.options.actions.constraintsAdd);

            var constraintsRemove = new ActionApplier({
                enable: function (action, actionId, ev, $el) {
                    // initial constraints
                    var hic = $el.data('fb.cl.has-initial-required-constraint'),
                        ic = $el.data('fb.cl.initial-constraints');

                    if (action.removeAllValidations === true) {
                        _.elementTransformer.transform('removeRequiredState', $el);
                    } else if ($.isArray(action.validation) && $.inArray('not_blank', action.validation) !== -1) {
                        _.elementTransformer.transform('removeRequiredState', $el);
                    }
                },
                disable: function (action, actionId, ev, $el) {
                    // initial constraints
                    var hic = $el.data('fb.cl.has-initial-required-constraint'),
                        ic = $el.data('fb.cl.initial-constraints');

                    if (hic === true) {
                        _.elementTransformer.transform('addRequiredState', $el);
                    }
                }
            }, this.options.actions.constraintsRemove);

            this.actions = {
                'toggleElement': toggleElement,
                'changeValue': changeValue,
                'triggerEvent': triggerEvent,
                'toggleClass': toggleClass,
                'toggleAvailability': toggleAvailability,
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

            this.setupInitialFields();
            this.parseConditions();
        },

        setupInitialFields: function () {

            var _ = this;

            //parse initial constraints
            _.$form.find('*[data-initial-constraints]').each(function () {
                var constraintString = $(this).data('initial-constraints'),
                    constraints,
                    $field,
                    hasCoreRequireField = false;

                if (constraintString) {
                    constraints = constraintString.split(',');
                }

                //append info to each checkbox/radio since the action also triggers on each checkbox/radio element!
                var $subFields = _.$form.find('*[id^=' + $(this).attr('id') + '_]');
                if ($subFields.length > 0) {
                    $field = $subFields;
                } else {
                    $field = $(this);
                }

                $(this).removeAttr('data-initial-constraints');
                $field
                    .data('fb.cl.initial-constraints', constraints)
                    .data('fb.cl.has-initial-required-constraint', $.inArray('not_blank', constraints) !== -1);
            })
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
                    var formDependingSelector = [],
                        actionId = 'action_' + blockId + '_' + i;
                    $.each(dependency.fields, function (fieldIndex, fieldName) {
                        formDependingSelector.push('*[name*="' + fieldName + '"]');
                        formDependingSelector.push('*[data-field-name*="' + fieldName + '"]');
                    });

                    var conditionSelector = {};
                    if (dependency.condition && dependency.condition.length > 0) {
                        conditionSelector = _.generateQualifiersSelector(dependency.condition, formSelector);
                    }

                    var actionOptions = _.generateActionOptions(dependency.action, actionId);
                    //no valid action found - skip field!
                    if (actionOptions === false) {
                        return true;
                    }

                    var $dependencies = _.$form.find(formDependingSelector.join(','));
                    if ($dependencies.length === 0) {
                        console.warn('no dependencies found. query was:', formDependingSelector);
                        return true;
                    }
                    var $conditionField = $dependencies.dependsOn(conditionSelector, actionOptions);
                    //console.log('add condition to', formDependingSelector, 'depends on: ', conditionSelector, 'actionOptions:', actionOptions);

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
                        $el = $(fieldSelector);

                    //if strict value is in comparator and element is a (maybe multiple) checkbox, stet selector to field with given value!
                    if ($el.length > 1 && $el.first().attr('type') === 'checkbox' && condition.comparator === 'is_value' && condition.value !== '') {
                        qualifiers['checked'] = true;
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
        generateActionOptions: function (action, actionId) {
            var _ = this,
                options = false,
                actionType = action.type;
            switch (actionType) {
                case 'toggleElement':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.toggleElement.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.toggleElement.onDisable.bind(null, action, actionId)
                    }
                    break;
                case 'changeValue':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.changeValue.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.changeValue.onDisable.bind(null, action, actionId)
                    }
                    break;
                case 'triggerEvent':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.triggerEvent.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.triggerEvent.onDisable.bind(null, action, actionId)
                    }
                    break;
                case 'toggleClass':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.toggleClass.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.toggleClass.onDisable.bind(null, action, actionId)
                    }
                    break;
                case 'toggleAvailability':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.toggleAvailability.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.toggleAvailability.onDisable.bind(null, action, actionId)
                    }
                    break;
                case 'constraintsAdd':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.constraintsAdd.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.constraintsAdd.onDisable.bind(null, action, actionId)
                    }
                    break;
                case 'constraintsRemove':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.constraintsRemove.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.constraintsRemove.onDisable.bind(null, action, actionId)
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