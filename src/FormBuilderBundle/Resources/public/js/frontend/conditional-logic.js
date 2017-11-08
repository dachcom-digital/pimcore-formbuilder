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
                    var actions = block.action;
                    $.each(actions, function (i, action) {
                        var actionFields = action.fields;
                        var dependFields = [];
                        $.each(actionFields, function (i, field) {
                            dependFields.push(field);
                        }.bind(this))
                        dependingStructure.push({'action': action, 'condition': block.condition, 'fields': dependFields});
                    }.bind(this))

                    //console.log(dependingStructure);
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
                        var $conditionField = $form.find(formDependingSelector.join(',')).dependsOn(conditionSelector, actionOptions);

                        console.log('add condition to', formDependingSelector, 'depends on: ', conditionSelector, 'actionOptions:' , actionOptions);

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
                    case 'value':
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
                    if (el.length > 1 && condition.type === 'value' && condition.value !== '') {
                        fieldSelector += '[value="' + condition.value + '"]';
                    }

                    conditionSelector[fieldSelector] = qualifiers
                });
            });

            return conditionSelector;
        },

        generateActionOptions: function (action) {
            var options = {};
            var actionType = action.type;
            switch (actionType) {
                case 'toggle':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: function (ev, $el, prevObject) {
                            $el.show().prev('label').show().parent('label').show();
                        },
                        onDisable: function (ev, $el, prevObject) {
                            $el.hide().prev('label').hide().parent('label').hide();
                        }
                    }
                    break;
                case 'value':
                    options = {
                        hide: false,
                        disable: false,
                        valueOnEnable: action.value
                    }
                    break;
                case 'event':
                    options = {
                        hide: false,
                        disable: false,
                        onEnable: function (ev, $el) {
                            console.log('formbuilder.cl.event.enable.' + action.event, $el);
                            $el.trigger('formbuilder.cl.event.enable.' + action.event);
                        },
                        onDisable: function (ev, $el) {
                            console.log('formbuilder.cl.event.disable.' + action.event, $el);
                            $el.trigger('formbuilder.cl.event.disable.' + action.event);
                        }
                    }
                    break;
                case 'class':
                    options = {
                        hide: false,
                        disable: false,
                        toggleClass: action.class
                    }
                    break;
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