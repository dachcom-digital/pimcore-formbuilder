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
use FormBuilderBundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Recaptcha3Type extends AbstractType
{
    public function __construct(protected Configuration $configuration)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $config = $this->configuration->getConfig('spam_protection');
        $reCaptchaConfig = $config['recaptcha_v3'];

        $view->vars['attr']['class'] = 're-captcha-v3';
        $view->vars['attr']['data-site-key'] = $reCaptchaConfig['site_key'];
        $view->vars['attr']['data-action-name'] = $options['action_name'];
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_recaptcha3_type';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped'      => false,
            'action_name' => 'homepage',
            'constraints' => [new Recaptcha3()],
        ]);
    }
}
