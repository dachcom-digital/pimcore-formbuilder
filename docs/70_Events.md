# Events

It's possible to add some events to every form submission.

## Pre Set Data Event
The `FORM_PRE_SET_DATA` event is dispatched at the beginning of the Form::setData() method.
It contains the form event and also some form builder settings.

@see \FormBuilderBundle\Event\Form\PreSetDataEvent
https://symfony.com/doc/current/form/events.html#a-the-formevents-pre-set-data-event
     
**Example**  
```php
<?php

use FormBuilderBundle\FormBuilderEvents;

[
    FormBuilderEvents::FORM_PRE_SET_DATA => 'formPreSetData'
];
```

## Post Set Data Event
The `FORM_POST_SET_DATA` event is dispatched at the end of the Form::setData() method.
This event is mostly here for reading data after having pre-populated the form.
It contains the form event and also some form builder settings.

@see \FormBuilderBundle\Event\Form\PostSetDataEvent
http://symfony.com/doc/current/form/events.html#b-the-formevents-post-set-data-event
     
**Example**  
```php
<?php

use FormBuilderBundle\FormBuilderEvents;

[
    FormBuilderEvents::FORM_POST_SET_DATA => 'formPostSetData'
];
```

## Pre Submit Event
The `FORM_PRE_SUBMIT` event is dispatched at the end of the Form::setData() method. 
This event is mostly here for reading data after having pre-populated the form. 
It contains the form event and also some form builder settings.

@see \FormBuilderBundle\Event\Form\PreSubmitEvent
https://symfony.com/doc/current/form/events.html#a-the-formevents-pre-submit-event
     
**Example**  
```php
<?php

use FormBuilderBundle\FormBuilderEvents;

[
    FormBuilderEvents::FORM_PRE_SUBMIT => 'formPreSubmitEvent'
];
```

## Submit Success
The `FORM_SUBMIT_SUCCESS` event occurs when a frontend form submission was successful.

**Example**  
```php
<?php

use FormBuilderBundle\FormBuilderEvents;

[
    FormBuilderEvents::FORM_SUBMIT_SUCCESS => 'formSubmitSuccess'
];
```

## Mail Pre Submit
The `FORM_MAIL_PRE_SUBMIT` event occurs before sending an email.

**Example**  
```php
<?php

use FormBuilderBundle\FormBuilderEvents;

[
    FormBuilderEvents::FORM_MAIL_PRE_SUBMIT => 'formMailPreSubmit'
];
```