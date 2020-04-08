var FormBuilderAreaWatcher = Class.create({

    initialize: function () {

        var _ = this;

        // watch form preset
        _.findElement('.pimcore_editable.pimcore_tag_select[data-real-name="formPreset"]', function ($el, cmp) {
            _.watchFormPresetDropdownElement($el, cmp);
        });

        // watch from and output workflow selector
        _.findElement('.pimcore_editable.pimcore_tag_select[data-real-name="formName"]', function ($formEl, formCmp) {
            var $parent;
            if (formCmp !== null && $formEl !== null) {
                $parent = _.getClosest($formEl, '.form-config-window');
                if ($parent !== null) {
                    _.findElement('.pimcore_editable.pimcore_tag_select[data-real-name="outputWorkflow"]', function ($workflowEl, workflowCmp) {
                        _.watchWorkflowDropdownElement($formEl, formCmp, $workflowEl, workflowCmp);
                    }, $parent);
                }
            }
        });
    },

    watchWorkflowDropdownElement: function ($formSelector, formCmp, $workflowSelector, workflowCmp) {

        var _ = this,
            outputWorkflowsData,
            noOutputWorkflowLabel,
            $parent = this.getClosest($workflowSelector, '.output-data-wrapper'),
            toggle = function (showOutputWorkflowSelector) {

                var $outputWorkflowSelectorBlock = $parent.querySelectorAll('.output-workflow-selector'),
                    $legacyOutputSelectorBlock = $parent.querySelectorAll('.legacy-output-selector');

                if ($outputWorkflowSelectorBlock.length === 0 || $legacyOutputSelectorBlock.length === 0) {
                    return;
                }

                if (showOutputWorkflowSelector === true) {
                    _.showEls($outputWorkflowSelectorBlock);
                    _.hideEls($legacyOutputSelectorBlock);
                } else {
                    _.hideEls($outputWorkflowSelectorBlock);
                    _.showEls($legacyOutputSelectorBlock);
                }
            };

        if ($formSelector === null || formCmp === null || $workflowSelector === null || workflowCmp === null) {
            console.warn('FormBuilder: Workflow components not found.');
            return;
        }

        outputWorkflowsData = $parent.getAttribute('data-output-workflows');
        noOutputWorkflowLabel = $parent.getAttribute('data-output-workflow-none-translation');

        if (outputWorkflowsData === null) {
            console.warn('FormBuilder: No output workflow definitions found.');
            return;
        }

        try {
            outputWorkflowsData = JSON.parse(outputWorkflowsData);
        } catch (e) {
            console.warn('FormBuilder Error while parsing output workflow data.', e);
            return;
        }

        formCmp.on('change', function () {
            var selectedFormId = this.getValue(),
                availableOutputWorkflows = [];
            Object.keys(outputWorkflowsData).forEach(function (formId) {
                if (parseInt(formId) === selectedFormId) {
                    availableOutputWorkflows = outputWorkflowsData[formId];
                }
            });

            var newStoreValues = availableOutputWorkflows.length > 0
                ? availableOutputWorkflows
                : [
                    ['none', noOutputWorkflowLabel]
                ];

            formCmp.setDisabled(true);
            workflowCmp.setDisabled(true);
            workflowCmp.setStyle('opacity', 0.5);

            // this is just for ux.
            setTimeout(function () {

                workflowCmp.setValue(newStoreValues[0][0]);
                workflowCmp.setStore(newStoreValues);

                formCmp.setDisabled(false);
                workflowCmp.setDisabled(false);
                workflowCmp.setStyle('opacity', 1);

                // change values
                toggle(availableOutputWorkflows.length > 0)
            }, 400);

        });
    },

    watchFormPresetDropdownElement: function ($formPresetSelector, cmp) {

        var _ = this,
            $parent = this.getClosest($formPresetSelector, '.form-config-window'),
            toggle = function (v) {

                var $previewFieldContainer = $parent.querySelectorAll('.preview-fields'),
                    $previewFields, $previewField;

                if ($previewFieldContainer.length === 0) {
                    return;
                }

                $previewFields = $previewFieldContainer[0].querySelectorAll('.preview-field');
                $previewField = $previewFieldContainer[0].querySelectorAll('.preview-field[data-name="' + v + '"]');

                if (v === 'custom') {
                    _.hideEls($previewFields);
                    _.hideEls($previewFieldContainer);
                    _.hideEls($previewField);
                } else {
                    _.hideEls($previewFields);
                    _.showEls($previewField);
                    _.showEls($previewFieldContainer);
                }
            };

        if (cmp === null) {
            console.warn('FormBuilder: Preset component not found.');
            return;
        }

        if ($parent === null) {
            console.warn('FormBuilder: Preset parent element not found.');
            return;
        }

        toggle(cmp.getValue());

        cmp.on('select', function () {
            toggle(this.getValue());
        });

    },

    findElement: function (selector, onComplete, $parent) {

        var _ = this,
            $elements = $parent ? $parent.querySelectorAll(selector) : document.querySelectorAll(selector);

        $elements.forEach(function (item) {

            var panicAttemt = 0, panicShutDown = 20,
                $el = item, $cmp, cmp;

            var interval = setInterval(function () {

                if (panicAttemt > panicShutDown) {
                    clearInterval(interval);
                }

                if ($cmp === null || $cmp === undefined || $cmp.length === 0) {
                    $cmp = $el.querySelectorAll('#' + $el.id + ' > div');
                } else {

                    clearInterval(interval);

                    //find extjs component
                    cmp = _.findComponentByElement($cmp[0]);

                    if (typeof onComplete === 'function') {
                        onComplete($el, cmp);
                    }
                }
            }, 100);
        });
    },

    findComponentByElement: function (node) {

        var topmost = document.body, target = node, cmp;
        while (target && target.nodeType === 1 && target !== topmost) {
            cmp = Ext.getCmp(target.id);

            if (cmp) {
                return cmp;
            }

            target = target.parentNode;
        }

        return null;
    },

    showEls: function (els) {
        els.forEach(function (item) {
            item.style.display = 'block';
        });
    },

    hideEls: function (els) {
        els.forEach(function (item) {
            item.style.display = 'none';
        });
    },

    getClosest: function (elem, selector) {

        if (!Element.prototype.matches) {
            Element.prototype.matches =
                Element.prototype.matchesSelector ||
                Element.prototype.mozMatchesSelector ||
                Element.prototype.msMatchesSelector ||
                Element.prototype.oMatchesSelector ||
                Element.prototype.webkitMatchesSelector ||
                function (s) {
                    var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                        i = matches.length;
                    while (--i >= 0 && matches.item(i) !== this) {
                    }
                    return i > -1;
                };
        }

        for (; elem && elem !== document; elem = elem.parentNode) {
            if (elem.matches(selector)) return elem;
        }
        return null;
    }

});

document.addEventListener('DOMContentLoaded', function (ev) {
    new FormBuilderAreaWatcher();
});
