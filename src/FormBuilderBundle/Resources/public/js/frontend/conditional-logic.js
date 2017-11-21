var formBuilderConditionalLogic = (function () {
    'use strict';
    var self = {

        $container: null,

        /**
         * Init container data
         * @param $container
         */
        init: function ($container) {
            self.$container = $container !== undefined ? $container : $('form.formbuilder');
            self.setupForms();
        },

        /**
         * add logic to each form.
         */
        setupForms: function () {
            var _ = this;
            this.$container.each(function () {
                var $form = $(this),
                    $clField = $form.find('input[name*="formCl"]'),
                    value = null, data = null;

                if ($clField.length === 0) {
                    return;
                }

                value = $clField.val();
                if (!value) {
                    return;
                }

                try {
                    data = $.parseJSON(value);
                } catch (e) {
                    console.warn('error while parsing conditional logic data. error was: ' + e);
                    return;
                }

                $.each(data, function (blockId, block) {
                    var dependingStructure = [];

                    //invalid conditional logic stack.
                    if(block === null) {
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
                    var formSelector = 'form[name="' + $form.prop('name') + '"]';
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
                        if(actionOptions === false) {
                            return true;
                        }
                        var $conditionField = $form.find(formDependingSelector.join(',')).dependsOn(conditionSelector, actionOptions);

                        //console.log('add condition to', formDependingSelector, 'depends on: ', conditionSelector, 'actionOptions:' , actionOptions);

                    });

                }.bind(this));
            });
        },

        /**
         * generate qualifiers depending on condition data.
         *
         * @param conditions
         * @param formSelector
         * @returns {{}}
         */
        generateQualifiersSelector: function (conditions, formSelector) {
            var conditionSelector = {};
            $.each(conditions, function (conditionId, condition) {
                var conditionType = condition.type,
                    qualifiers = {};
                switch (conditionType) {
                    case 'elementValue':
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

        generateActionOptions: function (action) {
            var options = false,
                actionType = action.type;
            switch (actionType) {
                case 'toggleElement':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: function (ev, $el, prevObject) {
                            if(action.state === 'hide') {
                                $el.val('');
                                $el.hide().prev('label').hide().parent('label').hide();
                            } else {
                                $el.show().prev('label').show().parent('label').show();
                            }
                        },
                        onDisable: function (ev, $el, prevObject) {
                            if(action.state === 'show') {
                                $el.val('');
                                $el.hide().prev('label').hide().parent('label').hide();
                            } else {
                                $el.show().prev('label').show().parent('label').show();
                            }
                        }
                    }
                    break;
                case 'changeValue':
                    options = {
                        hide: false,
                        disable: false,
                        valueOnEnable: action.value
                    }
                    break;
                case 'triggerEvent':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: function (ev, $el) {
                            $el.trigger(action.event + '.enable');
                        },
                        onDisable: function (ev, $el) {
                            $el.trigger(action.event + '.disable');
                        }
                    }
                    break;
                case 'toggleClass':
                    options = {
                        hide: false,
                        disable: false,
                        toggleClass: action.class
                    }
                    break;
                default:
                    options = false
            }

            return options;

        }
    };

    return {
        init: self.init
    };

})();

$(function () {
    'use strict';
    formBuilderConditionalLogic.init();
});