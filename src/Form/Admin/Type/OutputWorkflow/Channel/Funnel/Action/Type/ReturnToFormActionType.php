<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class ReturnToFormActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('populateForm', CheckboxType::class);
    }
}
