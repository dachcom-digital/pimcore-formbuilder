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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $config = $this->configuration->getConfig('spam_protection');
        $honeyPotConfig = $config['honeypot'];

        $attributes = [
            'autocomplete' => 'off',
            'tabindex'     => -1,
            'style'        => $honeyPotConfig['enable_inline_style'] === true ? 'position: absolute; left: -500%; top: -500%;' : ''
        ];

        if ($honeyPotConfig['enable_role_attribute'] === true) {
            $attributes['role'] = 'presentation';
        }

        $resolver->setDefaults([
            'required'       => false,
            'mapped'         => false,
            'data'           => '',
            'attr' => $attributes,
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

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'form_builder_honeypot';
    }
}
