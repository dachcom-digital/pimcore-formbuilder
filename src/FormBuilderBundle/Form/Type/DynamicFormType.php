<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Data\FormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormType extends AbstractType
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $addHoneypot = $this->configuration->getConfigFlag('use_honeypot_field');
        $spamProtectionConfig = $this->configuration->getConfig('spam_protection');
        $honeyPotConfig = $spamProtectionConfig['honeypot'];

        $builder
            ->add('formId', HiddenType::class, [
                'data' => $options['current_form_id'],
            ])
            ->add('formCl', HiddenType::class, [
                'data' => !empty($options['conditional_logic']) ? json_encode($options['conditional_logic']) : null,
            ]);

        if ($addHoneypot === true) {
            $builder->add($honeyPotConfig['field_name'], HoneypotType::class);
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
            'allow_extra_fields' => true,
            'csrf_protection'    => true,
            'data_class'         => FormData::class
        ]);
    }
}
