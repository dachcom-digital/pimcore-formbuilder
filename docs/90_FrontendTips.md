# Front-End Tips

## Required Fields
Just use some CSS to automatically add 'required field' asterisk to form inputs:

```css
.required:after { 
    content:" *"; 
}
```

## Hide the Honeypot for Real
Every rendered Form-Builder-Form adds a Honeypot-Field by default to prevent form spams (Read more about it [here](./03_SpamProtection.md).
To keep the field real as possible, we can't add a `display:hidden;` inline style - it could be exposed by bots.
Sometimes, Chrome will add some data to this field, if someone is using the autofill-feature.

To prevent this, just add some css:

```css
input[name$='[inputUserName]'] {
    display: none;
}

```

You can also disable the honeypot field entirely by setting the `use_honeypot_field`
[configuration flag](100_ConfigurationFlags.md) to false.

## Hide reCAPTCHA Badge on documents without pages
If you're using the reCAPTCHA globally, the script will add an badge at right corner on every page.
Since this is not quite pretty you may want to hide it on every page **except** on documents with a form:

```css
html:not(.form-builder-rec3-available) .grecaptcha-badge {
    visibility: hidden;
}
```

With this, the badge is only visible if a form builder form shows up. 
Read more about the reCAPTCHA Field [here](./03_SpamProtection.md).

## Multi File Validation 
You may want to add some more attention to the upload field if it's in a required state:

```css
.dynamic-multi-file.is-invalid .dropzone {
    border: 1px solid rgb(255, 0, 0);
}
```

## Conditional Logic 
If you want to use the [conditional logic](./81_ConditionalLogic.md) feature, you need to add a custom toggle class:

```css
.fb-cl-hide-element {
    display:none;
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

## Choice Meta Attributes
![image](https://user-images.githubusercontent.com/700119/117805038-cb250800-b258-11eb-8d18-e7433381e75f.png)

If you're using some choice meta attributes, you need to adjust your theme:

```twig
{# app/Resources/FormBuilderBundle/views/Form/Theme/bootstrap_4_layout.html.twig #}
{% extends "@!FormBuilder/Form/Theme/bootstrap_4_layout.html.twig" %}

{% block widget_attributes -%}

    {# example: tooltip meta #}
    
    {% if attr['data-meta-tooltip'] is defined %}
        {% set attr = attr|merge({'data-meta-tooltip': attr['data-meta-tooltip']|trans }) %}
    {% endif %}

    {{ parent() }}

{%- endblock widget_attributes %}

{% block checkbox_radio_label -%}

    {# example: relation meta #}
    
    {{ parent() }}
    
    {% if attr['data-meta-relation-' ~ app.request.locale ~ '-id'] is defined %}
        {{ pimcore_asset(attr['data-meta-relation-' ~ app.request.locale ~ '-id']).thumbnail('content').html|raw }}
    {% endif %}

{%- endblock checkbox_radio_label %}
```