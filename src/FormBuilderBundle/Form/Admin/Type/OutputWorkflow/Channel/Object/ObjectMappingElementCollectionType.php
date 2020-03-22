<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectMappingElementCollectionType extends AbstractType
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

            if (!is_array($data)) {
                return;
            }

            // remove all form fields that don't contain any child data
            // therefor we need to re-index the collection storage
            foreach ($data as $index => $collectionData) {

                if($collectionData['type'] !== 'form_field') {
                    continue;
                }

                if (isset($collectionData['childs']) && is_array($collectionData['childs']) && count($collectionData['childs']) === 0) {
                    unset($data[$index]);
                }
            }

            $newSortedData = array_values($data);

            $event->setData($newSortedData);
            $form->setData($newSortedData);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label'           => false,
            'auto_initialize' => false,
            'allow_add'       => true,
            'allow_delete'    => true,
            'by_reference'    => false,
            'entry_type'      => ObjectMappingElementConfigType::class,
            'delete_empty'    => function ($data) {

                if ($data['type'] === 'form_field') {
                    return !isset($data['childs']) || !is_array($data['childs']) || count($data['childs']) === 0;
                }

                return empty($data);
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
