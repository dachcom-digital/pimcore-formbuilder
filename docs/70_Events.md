# Events

It's possible to add some custom fields to every form.

1. Register a Listener:
```yaml
services:
    dachcom.event_listener.form_builder.listener:
        class: AppBundle\EventListener\FormListener
        tags:
            - { name: kernel.event_subscriber }
```

2. To add custom fields, you need to listen to the form pre set data event.

> **Important!** Do not add fields to the symfony form directly! 
> Form Builder will add additional data to your field, like template, order, email label and so on.

```php
<?php

namespace AppBundle\EventListener;

use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Storage\FormInterface as FormBuilderFormInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
        {
            return [
                FormBuilderEvents::FORM_PRE_SET_DATA => 'formPreSetData'
            ];
        }
        
    public function formPreSetData(PreSetDataEvent $event)
    {
        $formEvent = $event->getFormEvent();

        /** @var FormBuilderFormInterface $dataClass */
        $dataClass = $formEvent->getData();

        // get the form id/name from backend
        $formId = $dataClass->getId();
        $formName = $dataClass->getName();

        // get form options like the selected form preset
        $formOptions = $event->getFormOptions();

        var_dump($formOptions['formPreset']);

        // add your fields
        $dataClass->addDynamicField(
            
            //field name
            'your_dynamic_field_name',
            
            //field type
            EmailType::class,
            
            //all the field options
            [
                //no need to add translations here, fb will do this for you.
                'label'       => 'Your Field Label',
                'constraints' => [
                    new NotBlank(),
                ]
            ],
            
            //optional options
            [
                //add a template
                'template' => 'col-xs-6',
                
                //set the order of your field
                'order' => 10,
                
                //no need to add translations here, fb will do this for you.
                'email_label' => 'Your Field Email Label',

                // form builder usually tries to render the value for the email by itself.
                
                // 1. if you need to transform the value: use a closure
                //'email_value_transformer' => function(FormInterface $field, $value, $locale) {
                //    return 'YOUR_TRANSFORMED_VALUE'
                //},

                // 2. or a class method
                //'email_value_transformer' => [$this, 'checkValue']
            ]
        );
    }

    public function checkValue(FormInterface $field, $value, $locale)
    {
        return 'YOUR_TRANSFORMED_VALUE';
    }
}
```