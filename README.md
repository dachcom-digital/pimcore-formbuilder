# Pimcore Formbuilder

This Plugin is based on the [Zendformbuilder] (https://github.com/brainsbucket/Zendformbuilder) Plugin.
It's totally rewritten, offers a lot of new features and requires Pimcore 4.0.

Just download and install it into your plugin folder.

### Requirements
* Pimcore 4.0

### Features
* Build Forms in Backend easily.
* Place Forms everywhere you want with the form area
* Server validation
* Activate ajax mode to each form
* Define mail templates
* Bootstrap 3 ready
* V2 of reCAPTCHA

### Installation
Some installation advices. 

**Ajax**

If you want to use Ajaxforms, you need to load the FormBuilder Javascript Library. For example in your `lib/Website/Controller/Action.php`:
```php
$this->view->headScript()->appendFile('/plugins/Formbuilder/static/js/frontend/formbuilder.js');
```
Of course it's up to you, to modify this file for your needs.
**Attention:** Be sure that jQuery has been initialized, before you load formbuilder.js.

**CSS**

There is an css example in `/plugins/Formbuilder/static/css/frontend/formbuilder.css` (honeypot hide for example).
Feel free to copy its content into your main style.

**Override Templates**

To override the FormBuilder scripts, just create a formbuilder folder in your scripts folder to override templates:
 
 `/website/views/scripts/formbuilder/form/form.php`

### Styling
If you want to add some fancy radio / checkbox styling, just implement [this] (https://github.com/gurde/b3scr/blob/master/assets/css/b3scr.css) css from [gurde/b3scr] (https://github.com/gurde/b3scr).

### Mail Template
Place your form somewhere on your Website.
If you want to submit the form to the user, you can use your field names as placeholders. Formbuilder automatically will transform the field into the given address.
For Example you can set a placeholder called `%emailaddress%` (where *emailaddress* is the name of your form field) in the *To:* field ("Settings" Tab of your email template).

##### Available Properties
**mail_successfully_sent** *(String)*
Use the `mail_successfully_sent` propertie to define a message after the form has been successfully sent.

**mail_disable_default_mail_body** *(Checkbox)*
If `mail_disable_default_mail_body` is defined and checked, you need to add your own data to the mail template.
You can use all the field names as placeholder. For example:

```html
Hello %Text(firstname)!

Your Data:

Name: %Text(firstname)
Mail: %Text(mailaddress)
```