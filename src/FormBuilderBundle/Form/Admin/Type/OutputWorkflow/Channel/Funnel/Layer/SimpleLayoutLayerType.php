<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\PimcoreHrefType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SimpleLayoutLayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('layout', PimcoreHrefType::class);
    }
}
