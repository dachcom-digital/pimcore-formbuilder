# Javascript Plugins
We're providing some helpful Javascript Plugins to simplify your daily work with FormBuilder.
Of course it's up to you to copy those files into your project and modify them as required.

> Note: Be sure that jQuery has been initialized, before you load formbuilder.js.

## Overview
- [Core Plugin](#core-plugin)
- [Conditional Logic Extension](#conditional-logic-plugin)
- [Repeater Extension](#repeater-plugin)

## Core Plugin
This Plugin will enable the ajax functionality and also the multi file handling:

### Enable Plugin

```html
<script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/plugins/jquery.fb.core.form-builder.js') }}"></script>
```

```javascript
$(function () {
    $('form.formbuilder.ajax-form').formBuilderAjaxManager();
});
```
### Extended Usage
```javascript
$('form.formbuilder.ajax-form').formBuilderConditionalLogic({
    setupFileUpload: true, // initialize upload fields
    resetFormMethod: null, // reset method after success
    validationTransformer: {
        addValidationMessage: function($fields, messages) {
            console.log($fields, messages);
        },
        removeFormValidations: function($form) {
            console.log($form);
        }
    }
});
```

### Events

```javascript
$('form.ajax-form')
   .on('formbuilder.success', function(ev, message, redirect, $form) {
         console.log(message, redirect);
 }).on('formbuilder.error', function(ev, messages, $form) {
         console.log(messages);
 }).on('formbuilder.error-form', function (ev, messages, $form) {
        console.error('error-form', messages);
 }).on('formbuilder.error-field', function(ev, data, $form) {
         console.log(data.field, data.messages);
 }).on('formbuilder.fatal', function (ev, response, $form) {
        console.error('fatal', response.error, response.trace);
});
```

## Conditional Logic Plugin
This Plugin will enable the conditional logic functionality.

### Enable Plugin

```html
<script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/plugins/jquery.fb.ext.conditional-logic.js') }}"></script>
<script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/vendor/dependsOn/dependsOn.min.js') }}"></script>
```

```javascript
$(function () {
    $('form.formbuilder').formBuilderConditionalLogic();
});
```

### Extended Usage
```javascript
$('form.formbuilder').formBuilderConditionalLogic({
    conditions: {},
    actions: {
        toggleElement: {
            onEnable: function (action, actionId, ev, $el) {
                console.log(action, ev, $el);
            }
        }
    },
    elementTransformer: {
        hide: function($els) {
            $els.hide();
        }
    }
});
```

## Repeater Plugin
This Plugin will enable the repeater functionality.

### Enable Plugin

```html
<script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/plugins/jquery.fb.ext.repeater.js') }}"></script>
```

```javascript
$(function () {
    $('form.formbuilder').formBuilderRepeater();
});
```

### Extended Usage
```javascript
$forms.formBuilderRepeater({

    classes: {
        add: 'btn btn-special-class',
        remove: 'btn btn-special-class'
    },
    
    onRemove: function (cb) {
        var $containerBlock = $(this);
        $containerBlock.slideUp(1350, function () {
            $(this).remove();
            cb(); // always trigger the callback action!
        });
    },

    onAdd: function (newForm, cb) {
        var $container = $(this),
            $newForm = $(newForm);
        $newForm.insertAfter($container.find('span.add-block')).hide().slideDown(1000, function () {
            cb($newForm); // always trigger the callback action!
        });
    },

    renderCreateBlockElement: function (classes, text) {
        var $container = $(this);
        return $('<span/>', {
            'href': '#',
            'class': classes,
            'text': text
        });
    },

    allocateCreateBlockElement: function ($element) {
        var $container = $(this);
        $container.prepend($element);
    },

    renderRemoveBlockElement: function (classes, text) {
        var $containerBlock = $(this);
        return $('<span/>', {
            'href': '#',
            'class': classes,
            'text': text
        });
    },

    allocateRemoveBlockElement: function ($element) {
        var $containerBlock = $(this);
        $containerBlock.prepend($element);
    }

});
```