# Front-End Tips

## Required Fields
Just use some CSS to automatically add 'required field' asterisk to form inputs:

```css
.required:after { 
    content:" *"; 
}
```

## Hide the Honeypot for Real
Every rendered Formbuilder-Form adds an Honeypot-Field by default to prevent form spams.
To keep the field real as possible, we can't add a `display:hidden;` inline style - it could be exposed by bots.
Sometimes, Chrome will add some data to this field, if someone is using the autofill-feature.

To prevent this, just add some css:

```css
input[name$='[inputUserName]'] {
    display: none;
}

```

## HTML in Checkbox / Radio Labels
Formbuilder allows you to use HTML tags in checkbox and radio labels.
Just use the translation html editor to define some html label:

![labels with html](https://user-images.githubusercontent.com/700119/54492883-97453680-48ca-11e9-9abe-d43d1d89a505.png)

## HTML in Checkbox / Radio Labels
Formbuilder allows you to use HTML tags in checkbox and radio labels.
Just use the translation html editor to define some html label:

![labels with html](https://user-images.githubusercontent.com/700119/54492883-97453680-48ca-11e9-9abe-d43d1d89a505.png)

## Custom datepicker in date-fields
Add standard attribute class="custom-datepicker" to the field where you want it, untick html5-checkbox (available from tags 2.7.3 and 3.0.3 upwards)

and initialize your datepicker, p.e.: 
````javascript
$('.custom-datepicker').datepicker();
````

### Multilanguage support
For date-fields, there needs to be done more, since symfony expects the date to match following pattern:

```php
    # Symfony\Component\Form\Extension\Core\Type\DateType.php:
    const HTML5_FORMAT = 'yyyy-MM-dd';
```

Here is a way to implement the use of a different language for the frontend, while sending the correct format to symfony.

In this example, following packages are used: 
- "@chenfengyuan/datepicker": "^1.0.8",
- "moment": "^2.24.0",
        
... and only english and german is supported. (Make sure that your datepicker supports multiple languages and include them.)


```javascript
    var langIso = WEBSITE_CONFIG.language === 'en' ? 'en-GB' : 'de-DE',
        displayFieldSuffix = '_display';

    // datepicker formats
    $.fn.datepicker.languages['en-GB'] = {
        format: 'yyyy-MM-dd'
    };
    $.fn.datepicker.languages['de-DE'] = {
        format: 'd.M.yyyy'
    };

    $('form.formbuilder input.custom-datepicker').each(function () {
        // iconDiv is a wrapper element to show an icon in the input-field via css (since inputfields do not support ::after/::before);
        // iconDiv will contain a modified clone of the original element, because we need different date-formats for frontend and form-submission
        var iconDiv = document.createElement('div'),
            displayInputField = $(this).clone();

        $(iconDiv).addClass('date-icon');

        displayInputField.attr('name', null); // !! form-elements without name are not submitted
        displayInputField.attr('id', displayInputField.attr('id') + displayFieldSuffix);
        $(displayInputField).addClass('datepicker-display'); // for later selector
        
        $(iconDiv).append(displayInputField);

        this.style.display = 'none';
        $(this).parent('.form-group').append(iconDiv);
    });

    $('form.formbuilder input.custom-datepicker.datepicker-display').removeAttr('data-template').removeData('template').each(function () {
        var origId = this.id,
            displayFieldId = origId.substring(0, origId.length - displayFieldSuffix.length);

        $(this).datepicker({
            language: langIso,
            autoHide: true,
        }).on('change', function () {
            $('#' + displayFieldId).val(moment($('#' + origId).val(), $.fn.datepicker.languages[langIso].format.toUpperCase(), true).format('YYYY-MM-DD'));
        });
    });
```
