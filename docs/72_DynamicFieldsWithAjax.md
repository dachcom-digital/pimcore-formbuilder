# Dynamic Fields With Ajax

First, be sure you have enabled the [ajax submission mode](20_AjaxForms.md).

## Dynamic Fields and Data in Ajax Forms
For example, if you want to add a [dynamic field](71_DynamicFields.md) and fill it with data based on some request information.
If you're also using the ajax mode the data gets lost since the ajax request does not contain your special data anymore.
To fix this, you need to add some more form events:

```php
<?php

namespace AppBundle\EventListener;

use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
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

        /** @var FormDataInterface $formData */
        $formData = $formEvent->getData();
        /** @var FormDefinitionInterface $formDefinition */
        $formDefinition = $formData->getFormDefinition();

        //create some choices based on a request value.
        $entryId = $this->requestStack->getMasterRequest()->get('entry', null);

        // 1. Add a hidden field to keep the value,
        // since the request value gets lost during the ajax request.
        $formDefinition->addDynamicField('entry_id', HiddenType::class,['data' => $entryId]);

        // 2. Add the dynamic choice field.
        $this->addEventDateField($formDefinition, $entryId);
    }
    
    public function formPreSubmit(PreSubmitEvent $event)
    {
        $formOptions = $event->getFormOptions();
        if ($formOptions['form_preset'] !== 'dynamic_form') {
            return;
        }

        $formEvent = $event->getFormEvent();

        /** @var FormInterface $form */
        $form = $formEvent->getForm();
        /** @var array $formData */
        $formData = $formEvent->getData();
        /** @var FormDataInterface $dataClass */
        $dataClass = $form->getData();
        /** @var FormDefinitionInterface $formDefinition */
        $formDefinition = $dataClass->getFormDefinition();

        // remove entry_id: we don't need it anymore.
        $formDefinition->removeDynamicField('entry_id');

        //re-add the event date and populate it again!
        $this->addEventDateField($formDefinition, $formData['entry_id']);
    }

    private function addEventDateField(FormDefinitionInterface $formDefinition, $id)
    {
        $formDefinition->addDynamicField(
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