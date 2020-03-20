<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectMappingElementCollectionType extends AbstractType
{
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
