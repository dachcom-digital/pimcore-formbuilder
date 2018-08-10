# Ajax Forms

### Activation
First, check 'Ajax Submission' in your form configuration.

### Javascript Plugins
If you want to use ajax driven forms you need to load the FormBuilder Javascript Library. 
To enable this feature you need the FormBuilder Core Plugin. Please read [this documentation](91_Javascript.md) to learn how to **enable the required plugins**.

## Custom Fields and Data in Ajax Forms
For example, if you want to add a [dynamic choice field](71_CustomFields.md) and fill it with data based on some request information.
If you're also using the ajax mode the data gets lost since the ajax request does not contain your special data anymore.
To fix this, you need to add some more form events:

```php
<?php

namespace AppBundle\EventListener;

use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Storage\FormInterface as FormBuilderFormInterface;
use FormBuilderBundle\Storage\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormBuilderListener implements EventSubscriberInterface
{
    protected $requestStack;
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormBuilderEvents::FORM_PRE_SET_DATA  => 'formPreSetData',
            FormBuilderEvents::FORM_PRE_SUBMIT    => 'formPreSubmit'
        ];
    }

    public function formPreSetData(PreSetDataEvent $event)
    {
        $formEvent = $event->getFormEvent();
        $formOptions = $event->getFormOptions();

        //only apply this to a special preset
        if ($formOptions['form_preset'] !== 'dynamic_form') {
            return;
        }

        /** @var FormBuilderFormInterface $formClass */
        $formData = $formEvent->getData();
        
        //create some choices based on a request value.
        $entryId = $this->requestStack->getMasterRequest()->get('entry');

        // 1. Add a hidden field to keep the value
        // since the request value gets lost during the ajax request.
        $formData->addDynamicField(
            'entry_id',
            HiddenType::class,
            [
                'data' => $entryId
            ]
        );

        // 2. Add the dynamic choice field.
        $this->addEventDateField($formData, $entryId);
    }
    
    public function formPreSubmit(PreSubmitEvent $event)
    {
        $formOptions = $event->getFormOptions();
        if ($formOptions['form_preset'] !== 'dynamic_form') {
            return;
        }

        $formEvent = $event->getFormEvent();
        $form = $formEvent->getForm();
        $formData = $formEvent->getData();        

        //remove the entry id field, since we don't need it anymore!
        $formEvent->getData()->removeDynamicField('entry_id');
        
        //re-add the event date and populate it again!
        $this->addEventDateField($form->getData(), $formData['entry_id']);
        
    }

    private function addEventDateField(FormInterface $form, $id)
    {
        $form->addDynamicField(
            'event_date',
            ChoiceType::class,
            [
                'label'          => 'Date',
                'required'       => true,
                
                /*
                 * set error_bubbling to "true" if you want to submit error
                 * to main form as "general error".
                 */
                'error_bubbling' => false, 
                
                /* 
                * Uncomment section if you want some expanded custom bootstrap checkboxes
                *
                * 'multiple' => true,
                * 'expanded' => true,
                * 'label_attr' => [
                *     'class' => 'checkbox-custom'
                * ],
                */
                'choices'        => $this->getChoices($id),
                'constraints'    => [
                    new NotBlank()
                ]
            ],
        
            /*
             * This third argument is FormBuilder related stuff.
             */
            [
                'template' => 'col-12',
                'order'    => 10
            ]
        );
    }
    
    private function getChoices($id = null)
    {
        if (empty($id)) {
            return [
                'Test 1' => 'test1',
                'Test 2' => 'test2',
                'Test 3' => 'test3'
            ];
        }

        return [
            'Test 1 (for ' . $id . ')' => 'test1',
            'Test 2 (for ' . $id . ')' => 'test2',
            'Test 3 (for ' . $id . ')' => 'test3'
        ];
    }
}
```