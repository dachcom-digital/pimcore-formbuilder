<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Recaptcha3Type extends AbstractType
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
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $config = $this->configuration->getConfig('recaptcha_v3');

        $view->vars['attr']['class'] = 're-captacha-v3';
        $view->vars['attr']['data-site-key'] = $config['site_key'];
        $view->vars['attr']['data-action-name'] = $options['action_name'];
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return HiddenType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'form_builder_recaptcha3_type';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped'      => false,
            'action_name' => 'homepage',
            'constraints' => [new Recaptcha3()],
        ]);
    }
}
