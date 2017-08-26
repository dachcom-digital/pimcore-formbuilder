# Ajax Forms

First, check 'Ajax Submission' in your form configuration.

If you want to use ajax driven forms you need to load the FormBuilder Javascript Library. 
For example in your `app/Resources/views/layout.html.twig:

```html
<script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/formbuilder.js') }}"></script>
```

If you want to hook into the ajax form events, you may use this example:

```javascript
$('form.ajax-form')
   .on('formbuilder.success', function(ev, message, redirect, $form) {
         console.log(message, redirect);
 }).on('formbuilder.error', function(ev, message, $form) {
         console.log(messages);
 }).on('formbuilder.error-field', function(ev, data, $form) {
         console.log(messages);
```

Of course it's up to you to modify this file for your needs.

> Note: Be sure that jQuery has been initialized, before you load formbuilder.js.
