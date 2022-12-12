<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class FunnelActionCoreConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('ignoreInvalidFormSubmission', CheckboxType::class);
    }
}
