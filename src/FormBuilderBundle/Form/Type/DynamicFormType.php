<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Storage\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formId', HiddenType::class, [
                'data' => $options['current_form_id'],
            ])
            ->add('formCl', HiddenType::class, [
                'data' => !empty($options['conditional_logic']) ? json_encode($options['conditional_logic']) : null,
            ]);

        if ($options['add_honeypot']) {
            $builder->add('inputUserName', HoneypotType::class);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'current_form_id'    => 0,
            'conditional_logic'  => [],
            'add_honeypot'       => true,
            'allow_extra_fields' => true,
            'csrf_protection'    => true,
            'data_class'         => Form::class
        ]);
    }
}
