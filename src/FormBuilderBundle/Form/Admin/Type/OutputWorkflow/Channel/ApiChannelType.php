<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Api\DataMappingElementCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ApiChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('apiProvider', TextType::class);
        $builder->add('apiMappingData', DataMappingElementCollectionType::class);
        $builder->add('apiConfiguration', CollectionType::class, ['allow_add' => true, 'entry_type' => TextType::class]);
    }
}
