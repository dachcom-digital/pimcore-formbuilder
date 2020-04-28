/*
 *  Project: PIMCORE FormBuilder
 *  Extension: Conditional Logic
 *  Since: 2.2.1
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

    function ElementTransformer($form, options, formTemplate) {

        var getContainerAwareFields = function ($els, $form) {
            var $fields,
                isContainerAware = false,
                $containerField = $els.first().closest('.formbuilder-container', $form[0]),
                isContainerField = $containerField.length > 0;

            if (isContainerField === true) {
                isContainerAware = true;
                $fields = $containerField;
            } else {
                $fields = $els;
            }

            return {'isContainerAware': isContainerAware, 'fields': $fields};
        };

        this.$form = $form;
        this.formTemplate = formTemplate;
        this.userMethods = options;
        this.themeTransform = {
            'bootstrap3': {
                show: function ($els, className) {
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).removeClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
                hide: function ($els, className) {
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        if (data.isContainerAware === false) {
                            data.fields.val('').prop('selectedIndex', 0);
                        }
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).addClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
                addClass: function ($els, className) {
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).addClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
                removeClass: function ($els, className) {
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).removeClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
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
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).removeClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
                hide: function ($els, className) {
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        if (data.isContainerAware === false) {
                            data.fields.val('').prop('selectedIndex', 0);
                        }
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).addClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
                addClass: function ($els, className) {
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).addClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
                removeClass: function ($els, className) {
                    $els.each(function (i, el) {
                        var data = getContainerAwareFields($(el), this.$form);
                        data.fields.each(function (i, dataElement) {
                            $(dataElement).closest('.formbuilder-row', this.$form[0]).removeClass(className);
                        }.bind(this));
                    }.bind(this));
                }.bind(this),
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
                case 'bootstrap_4_layout':
                case 'bootstrap_4_horizontal_layout':
                    return this.themeTransform.bootstrap4[action].apply(null, args);
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
        this.formRuntimeOptions = {};
        this.logic = {};
        this.actions = {};
        this.conditions = {};
        this.elementTransformer = new ElementTransformer(this.$form, this.options.elementTransformer, this.formTemplate);

        this.setupConditionProcessor();
        this.setupActionProcessor();
        this.init();

        this.$form.addClass('fb-cl-initialized');
    }

    $.extend(ConditionalLogic.prototype, {

        setupConditionProcessor: function () {

            var _ = this,
                elementValue,
                outputWorkflow;

            elementValue = new QualifiersApplier({
                check: function (condition) {
                    var qualifiers = {};
                    switch (condition.comparator) {
                        case 'is_greater':
                            qualifiers['greater'] = function (val) {
                                return parseFloat(val) > parseFloat(condition.value);
                            };
                            break;
                        case 'is_less':
                            qualifiers['less'] = function (val) {
                                return parseFloat(val) < parseFloat(condition.value);
                            };
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
                            qualifiers['contains'] = $.map(condition.value.split(','), function (e) {
                                return $.trim(e)
                            });
                            break;
                        case 'is_checked':
                            qualifiers['not'] = [undefined, ''];
                            qualifiers['checked'] = true;
                            break;
                        case 'is_not_checked':
                            qualifiers['checked'] = false;
                            break;
                    }

                    return qualifiers;
                }
            }, this.options.conditions.elementValue);

            outputWorkflow = new QualifiersApplier({
                check: function (condition) {
                    if (!condition.hasOwnProperty('outputWorkflows') || !$.isArray(condition.outputWorkflows)) {
                        return true;
                    }

                    if (!_.formRuntimeOptions.hasOwnProperty('form_output_workflow')) {
                        return true;
                    }

                    return $.inArray(_.formRuntimeOptions.form_output_workflow, condition.outputWorkflows) !== -1;
                }
            }, this.options.conditions.outputWorkflow);

            this.conditions = {
                elementValue: elementValue,
                outputWorkflow: outputWorkflow
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
                        } else if ($(this).is('textarea')) {
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
                $rtoField = this.$form.find('input[name*="formRuntimeData"]');

            if ($clField.length > 0 && $clField.val()) {
                try {
                    this.logic = $.parseJSON($clField.val());
                } catch (e) {
                    console.warn('error while parsing conditional logic data. error was: ' + e);
                    return;
                }
            }

            if ($rtoField.length > 0 && $rtoField.val()) {
                try {
                    this.formRuntimeOptions = $.parseJSON($rtoField.val());
                } catch (e) {
                    console.warn('error while parsing form runtime options data. error was: ' + e);
                    return;
                }
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
                    $field;

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
                    }.bind(this));
                    dependingStructure.push({'action': action, 'condition': block.condition, 'fields': dependFields});
                }.bind(this));

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

                    var dependsOnQuery = $dependencies.dependsOn(conditionSelector['and'], actionOptions);
                    if (Object.keys(conditionSelector['or']).length > 0) {
                        dependsOnQuery.or(conditionSelector['or']);
                    }

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
                filteredConditions = [],
                cancelCondition = false,
                conditionSelector = {'and': {}, 'or': {}};

            // check conditions first!
            $.each(conditions, function (conditionId, condition) {

                var conditionType = condition.type,
                    conditionData = {};

                switch (conditionType) {
                    case 'elementValue':
                        conditionData = _.conditions.elementValue.onCheck(condition);
                        break;
                    case 'outputWorkflow':
                        conditionData = _.conditions.outputWorkflow.onCheck(condition);
                        break;
                }

                // condition is boolean and not allowed to proceed.
                if (conditionData === false) {
                    cancelCondition = true;
                    return false;
                }

                // condition is boolean and allowed to proceed but has not qualifiers.
                if (conditionData === true) {
                    return;
                }

                filteredConditions.push({
                    condition: condition,
                    qualifiers: conditionData
                });

            });

            if (cancelCondition === true) {
                return conditionSelector;
            }

            $.each(filteredConditions, function (conditionId, filteredCondition) {

                var condition = filteredCondition.condition,
                    qualifiers = filteredCondition.qualifiers;

                $.each(condition.fields, function (fieldIndex, field) {
                    var fieldSelector = formSelector + ' *[name*="' + field + '"]',
                        $el = $(fieldSelector),
                        isCheckbox = $el.first().attr('type') === 'checkbox';

                    if (isCheckbox) {
                        if (condition.comparator !== 'is_not_checked') {
                            qualifiers['checked'] = true;
                        }
                    } else {
                        if (condition.comparator === 'is_not_checked') {
                            qualifiers['values'] = [undefined, ''];
                        }
                    }

                    //if strict value is in comparator and element is a (maybe multiple) checkbox, set selector to field with given value!
                    if ($el.length > 1 && isCheckbox === true) {
                        if (condition.comparator === 'is_value' && condition.value !== '') {
                            fieldSelector += '[value="' + condition.value + '"]';
                            conditionSelector['and'][fieldSelector] = qualifiers
                        } else if (condition.comparator === 'contains' && condition.value !== '') {
                            $.each(condition.value.split(','), function (index, value) {
                                var multiFieldSelector = fieldSelector + '[value="' + $.trim(value) + '"]',
                                    section = index === 0 ? 'and' : 'or';
                                conditionSelector[section][multiFieldSelector] = qualifiers
                            });
                        } else {
                            conditionSelector['and'][fieldSelector] = qualifiers
                        }
                    } else {
                        conditionSelector['and'][fieldSelector] = qualifiers
                    }
                });
            });

            return conditionSelector;
        },

        /**
         *
         * @param action
         * @param actionId
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
                    };
                    break;
                case 'changeValue':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.changeValue.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.changeValue.onDisable.bind(null, action, actionId)
                    };
                    break;
                case 'triggerEvent':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.triggerEvent.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.triggerEvent.onDisable.bind(null, action, actionId)
                    };
                    break;
                case 'toggleClass':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.toggleClass.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.toggleClass.onDisable.bind(null, action, actionId)
                    };
                    break;
                case 'toggleAvailability':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.toggleAvailability.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.toggleAvailability.onDisable.bind(null, action, actionId)
                    };
                    break;
                case 'constraintsAdd':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.constraintsAdd.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.constraintsAdd.onDisable.bind(null, action, actionId)
                    };
                    break;
                case 'constraintsRemove':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: _.actions.constraintsRemove.onEnable.bind(null, action, actionId),
                        onDisable: _.actions.constraintsRemove.onDisable.bind(null, action, actionId)
                    };
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