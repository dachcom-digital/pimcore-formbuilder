# Pimcore Formbuilder

This Plugin is based on the [Zendformbuilder] (https://github.com/brainsbucket/Zendformbuilder) Plugin.
It's totally rewritten, offers a lot of new features and requires Pimcore 4.0.

## Requirements
* Pimcore 4.0

## Features
* Build Forms in Backend easily.
* Place Forms everywhere you want with the form area
* Server validation
* Activate ajax mode to each form
* Define mail templates
* Bootstrap 3 ready
* V2 of reCAPTCHA

## Installation
Some installation advices. 

**Handcrafted Installation**    
1. Download Plugin  
2. Rename it to `Formbuilder`  
3. Place it in your plugin directory  
4. Activate & install it through backend 

**Composer Installation**  
1. Add code below to your `composer.json`   
2. Activate & install it through backend  

```json
"require" : {
    "dachcom-digital/pimcore-formbuilder" : "1.2.6",
}
```



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

**Misc**  
Add `/website/var/formbuilder/` to your `.gitignore` since forms only can be created in backend.

## Styling
If you want to add some fancy radio/checkbox styling, just implement [this] (https://github.com/gurde/b3scr/blob/master/assets/css/b3scr.css) css from [gurde/b3scr] (https://github.com/gurde/b3scr).

## Mail Template
Place your form somewhere on your Website.
If you want to submit the form to the user, you can use your field names as placeholders. Formbuilder automatically will transform the field into the given address.
For Example you can set a placeholder called `%emailaddress%` (where *emailaddress* is the name of your form field) in the *To:* field ("Settings" Tab of your email template).

### Available Properties

Add those properties to your mail template.

#### mail_successfully_sent *(String|Document|Snippet)*

Use the `mail_successfully_sent` property to define a message after the form has been successfully sent.
There are three options:

**String**  
Use a String to return a simple text like "Thank you for your message".

**Snippet**  
Use a Snippet to return some complex html stuff.

**Document**  
Add a Document to redirect after your form has been successfully submitted.

#### mail_disable_default_mail_body *(Checkbox)*  

If the `mail_disable_default_mail_body` property is defined and checked, you need to add your own data to the mail template.
You can use all the field names as placeholder. For example:

```html
Hello %Text(firstname);!

Your Data:

Name: %Text(firstname);
Mail: %Text(mailaddress);
```

## Ajax Forms

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

## Events

**formbuilder.form.preCreateForm**  
Use this Event to manipulate and extend Forms dynamically. 

*Example*

```php
\Pimcore::getEventManager()->attach(
    'formbuilder.form.preCreateForm', 
    function( \Zend_EventManager_Event $e ) 
    {
        $form = $e->getParam('form');
        
        $frontController = \Zend_Controller_Front::getInstance();
        $var = $frontController->getRequest()->getParam('getVarOne');
    
        if ( !empty( $var) ) 
        {
            $form->addElement(
                'hidden',
                'getVarOne',
                [
                    'label' => 'Get Var One',
                    'value' => $var,
                    'order' => '-1'
                ]
            );
    
        }
    
        $e->stopPropagation(true);
    
        return $form;
    }
);
```
## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)