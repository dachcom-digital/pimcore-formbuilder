<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Api;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataMappingElementCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($data) {
                return $data;
            },
            function ($data) {

                if (!is_array($data)) {
                    return $data;
                }

                foreach ($data as $index => $collectionData) {

                    $hasChildren = isset($collectionData['children']) && count($collectionData['children']) > 0;

                    if ($hasChildren === true) {
                        continue;
                    }

                    if (count($collectionData['config']['apiMapping']) === 0) {
                        unset($data[$index]);
                    }

                }

                return array_values($data);
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'entry_type'   => DataMappingElementConfigType::class,
            'allow_add'    => true,
            'allow_delete' => true
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
