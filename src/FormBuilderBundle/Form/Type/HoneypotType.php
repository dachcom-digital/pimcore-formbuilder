<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Blank;

class HoneypotType extends AbstractType
{
    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $config = $this->configuration->getConfig('spam_protection');
        $honeyPotConfig = $config['honeypot'];

        $resolver->setDefaults([
            'required'       => false,
            'mapped'         => false,
            'data'           => '',
            'attr'           => [
                'autocomplete' => 'off',
                'tabindex'     => -1,
                'style'        => $honeyPotConfig['enable_inline_style'] === true ? 'position: absolute; left: -500%; top: -500%;' : ''
            ],
            'constraints'    => [
                new Blank(
                    [
                        'groups'  => [
                            Constraint::DEFAULT_GROUP,
                            'honeypot'
                        ],
                        'message' => 'An error has occurred, please refresh the page and try again.',
                    ]
                )
            ],
            'error_bubbling' => true,
            'label'          => false
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_honeypot';
    }
}
