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
If you want to use Ajaxforms, you need to load the FormBuilder Javascript Library. 
For example in your `lib/Website/Controller/Action.php`:

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

## Backend

### Label Placeholder
It's possible to add some placeholder in labels which will be transformed to links automatically.
A Label may look like this: `I'm ok with your evil [document id=1]conditions[/document].`

*Document*  
Creates a Link to a Dokument

*Snippet*  
Creates a empty link element with some data attributes (id, type). If you want to link to the snippet content, you need to define your own methods or routes to do so.

*Document*  
Creates a Link to a Asset

### Form Presets
It's possible to define some form presets.

In your /website/var/config/ folder you'll find a `formbuilder_configurations.php` file. You may want to add some presets there. For example:

```php

<?php 

return [
    1 => [
        "id" => 1,
        "key" => "form.area.presets",
        "data" => [

            "example" => [
            
                /*
                   Required (string)
                   Add a nice name for the preset dropdown.
                */
                "niceName" => "Example Form",
                
                /*
                   Optional (string)
                   Add some Description. (Allowed Tags: strong,em,p,span)
                **/
                "adminDescription" => "Example Form. For your Health.",
                
                /*
                   Optional (array|string)
                   Restrict preset to an active domain. Use site main domains!
                */
                "site" => ["example-page.com"],
                
                /*
                   Required (array|string)
                   Set mail template which should be used to sent your form. 
                   If you want to use language based templates, use an array with language keys.
                */
                "mail" => [
                   "en" => "/en/emails/example-form"
                ],
                
                /*
                   Optional (empty|array|string)
                   Set mail template which should be used to sent a copy of your form. 
                   If you want to use language based templates, use an array with language keys.
                */
                "mailCopy" => "/en/emails/example-form-copy"
              
            ]

        ],
        "creationDate" => 1480684113,
        "modificationDate" => 1480684113
    ]
];
```

### Form Container

```php

<?php 

return [
    1 => [
        "id" => 1,
        "key" => "form.area.groupTemplates",
        "data" => [

            /*
               Required (string)
               template id
            */
            "half" => [
                "niceName" => "Half Layout",
                
                /*
                   apply decorators to container itself
                */
                "group" => [
                    "decorators" => [
                        [
                            ['wrapperField' => 'HtmlTag'],
                            ['tag' => 'div', 'class' => 'col-xs-12 col-sm-6']
                        ]
                    ]
                ],
                
                /*
                   apply decorators to elements IN container
                */
                "elements" => [
                    "decorators" => [

                    ]
                ]
            ],
            
            "row" => [
                "niceName" => "Row Layout",
                "group" => [
                    "decorators" => [
                        [
                            ['rowField' => 'HtmlTag'],
                            ['tag' => 'div', 'class' => 'row']
                        ]
                    ]
                ],
                "elements" => [
                    "decorators" => [

                    ]
                ]
            ]

        ],
        "creationDate" => 1480684113,
        "modificationDate" => 1480684113
    ]
];
```

## Styling
If you want to add some fancy radio/checkbox styling, just implement [this] (https://github.com/gurde/b3scr/blob/master/assets/css/b3scr.css) css from [gurde/b3scr] (https://github.com/gurde/b3scr).

## Mail Template
Place your form somewhere on your Website.
If you want to submit the form to the user, you can use your field names as placeholders. Formbuilder automatically will transform the field into the given address.
For Example you can set a placeholder called `%emailaddress%` (where *emailaddress* is the name of your form field) in the *To:* field ("Settings" Tab of your email template).  

**Subject**  
You also may want to add some placeholder in the subject of your mail template. To do so, just add a placeholder like `%emailaddress%` to the subject field. They will get transformed automatically.

### Available Properties

Add those properties to your mail template.

#### mail_successfully_sent *(String|Document|Snippet)*

Use the `mail_successfully_sent` property to define a message after the form has been successfully sent.
There are three options:

*String*  
Use a String to return a simple text like "Thank you for your message".

*Snippet*  
Use a Snippet to return some complex html stuff.

*Document*  
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

#### mail_ignore_fields *(String)*

In some cases, you don't want to send specific fields via mail. For example, if you send a copy to the user.
To do so, just define the `mail_ignore_fields` property in your email template. Add one or multiple (comma separated) fields as string.  
*Notice:* The field name needs be identical to the field name in your form configuration.

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

**formbuilder.form.preSendData**  
Use this Event to manipulate form data before they get submitted.

**formbuilder.form.label.placeholder**  
Use this Event to manipulate label placeholder attributes. See Section *Backend* => *Label Placeholder*.

*Example*

```php
\Pimcore::getEventManager()->attach(
    'formbuilder.label.placeholder',
    function( \Zend_EventManager_Event $e ) 
    {
        $params = $e->getParam('params');
        $params['class'] = $params['class'] . ' your-custom-class';

        $e->stopPropagation(true);
        
        return $params;
    }
);
```

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)