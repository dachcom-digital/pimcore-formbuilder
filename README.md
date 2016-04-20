# Pimcore Formbuilder

This Plugin is based on the [Zendformbuilder] (https://github.com/brainsbucket/Zendformbuilder) Plugin.
It's totally rewritten, offers a lot of new features and requires Pimcore 4.0.

Just download and install it into your plugin folder.

### Requirements
* Pimcore 4.0

### Features
* Build Forms in Backend easily. :)
* Place Forms everywhere you want with the form area
* Server validation
* Activate ajax mode to each form
* Define mail templates

###Styling
If you want to add some fancy radio / checkbox styling, just implement [this] (https://github.com/gurde/b3scr/blob/master/assets/css/b3scr.css) css from [gurde/b3scr] (https://github.com/gurde/b3scr).

###Mail Template

Place your Form somewhere on your Website.

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
