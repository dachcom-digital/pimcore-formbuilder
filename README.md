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

### Important installation notes

With Version 1.0.6 the you need the [cgsmith/zf1-recaptcha-2] (https://github.com/cgsmith/zf1-recaptcha-2) repository. 
Because we're awaiting a PR approval and composer [does not allow loading repositories recursively] (https://getcomposer.org/doc/faqs/why-can't-composer-load-repositories-recursively.md), you **need** to add a composer repository to your root composer.json.
Hopefully this isn't necessary in Version 1.0.7. Add this to your `repositories` array:

```json
"repositories": [
    {
    "type": "package",
    "package": 
        {
            "name": "cgsmith/zf1-recaptcha-2",
            "version": "dev-master",
            "source": {
                "url": "https://github.com/solverat/zf1-recaptcha-2",
                "type": "git",
                "reference": "dev-solverat"
            }
        }
    }
]
```

Also add this to your composer.json:


```json
{
    "minimum-stability" : "dev",
    "prefer-stable" : true
}
```

###Styling
If you want to add some fancy radio / checkbox styling, just implement [this] (https://github.com/gurde/b3scr/blob/master/assets/css/b3scr.css) css from [gurde/b3scr] (https://github.com/gurde/b3scr).

###Mail Template

Place your Form somewhere on your Website.
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
