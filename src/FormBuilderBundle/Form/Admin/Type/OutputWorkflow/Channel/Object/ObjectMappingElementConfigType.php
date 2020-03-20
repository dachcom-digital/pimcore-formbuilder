<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ObjectMappingElementConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', TextType::class);
        $builder->add('fieldType', TextType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $data = $event->getData();
            $form = $event->getForm();

            // reset old form data
            $form->setData(null);

            if (isset($data['childs'])) {
                $form->add('childs', ObjectMappingElementCollectionType::class);
            }

            if (isset($data['type'])) {
                $form->add('config', FieldConfigType::class, ['config_type' => $data['type'], 'field_config_type' => $data['fieldType']]);
            }
        });
    }

}
