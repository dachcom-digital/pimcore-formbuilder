<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Blank;

class HoneypotType extends AbstractType
{
    public function __construct(protected Configuration $configuration)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
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
            'attr'           => $attributes,
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
