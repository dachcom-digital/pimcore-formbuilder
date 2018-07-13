<?php

namespace FormBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Blank;

class HoneypotType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required'       => false,
            'mapped'         => false,
            'data'           => '',
            'attr'           => [
                'autocomplete' => 'off',
                'tabindex'     => -1,
                'style'        => 'position: absolute; left: -500%; top: -500%;'
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