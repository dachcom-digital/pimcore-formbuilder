var FormBuilderAreaWatcher = Class.create({

    initialize: function () {

        var _ = this,
            $formPresetElements = document.querySelectorAll('.pimcore_editable.pimcore_tag_select[data-real-name="formPreset"]');

        $formPresetElements.forEach(function (item) {

            var panicAttemt = 0, panicShutDown = 20,
                $el = item, $cmp, cmp;


            var interval = setInterval(function () {

                if (panicAttemt > panicShutDown) {
                    clearInterval(interval);
                }

                if ($cmp === null || $cmp === undefined || $cmp.length === 0) {
                    $cmp = $el.querySelectorAll('#' + $el.id + ' > div');
                    panicAttemt++;
                } else {

                    clearInterval(interval);

                    //find extjs component
                    cmp = _.findComponentByElement($cmp[0]);

                    //watch drop down select event!
                    _.setupDropdownElement($el, cmp);

                }
            }, 100);
        });
    },

    setupDropdownElement: function ($formPresetSelector, cmp) {

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
            console.warn('cmp not found');
            return false;
        }

        if ($parent === null) {
            console.warn('parent not found');
            return false;
        }

        toggle(cmp.getValue());

        cmp.on('select', function () {
            toggle(this.getValue());
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
