# Custom Fields with Events

It's possible to add some custom fields (we call them dynamic fields) to every form.

> Info: It's always a good idea to use [presets](60_Presets.md) if you're using custom fields.

1. Register a Listener:
```yaml
services:
    AppBundle\EventListener\FormListener:
        autowire: true
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
use FormBuilderBundle\Form\Data\FormDataInterface;
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

        /** @var FormDataInterface $formData */
        $formData = $formEvent->getData();

        // get the form id/name from backend
        $formId = $formData->getFormDefinition()->getId();
        $formName = $formData->getFormDefinition()->getName();

        // get form options like the selected form preset
        $formOptions = $event->getFormOptions();

        //add dynamic fields depending on custom presets, is available.
        var_dump($formOptions['form_preset']);

        // add your fields
        $formData->getFormDefinition()->addDynamicField(
            
            //field name
            'your_dynamic_field_name',
            
            //field type
            EmailType::class,
            
            //all the field options
            [
                //no need to add translations here, fb will do this for you.
                'label'       => 'Your Field Label',
                'help_text'   => 'Help Text for your Custom Field',
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
                
                //optional: add a output transformer (only affects this element)
                'output_transformer' => 'my_output_transformer',
            ]
        );
    }

    public function checkValue(FormInterface $field, $value, $locale)
    {
        return 'YOUR_TRANSFORMED_VALUE';
    }
}
```
> Info: Checkout the [ajax form example](20_AjaxForms.md) to learn how to combine dynamic fields with ajax forms.
