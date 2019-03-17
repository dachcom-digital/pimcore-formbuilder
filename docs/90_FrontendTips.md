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

