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

use FormBuilderBundle\Tool\MathCaptchaProcessor;
use FormBuilderBundle\Validator\Constraints\MathCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MathCaptchaType extends AbstractType
{
    public function __construct(protected MathCaptchaProcessor $mathCaptchaProcessor)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $stamp = $this->mathCaptchaProcessor->generateStamp();
        $challenge = $this->mathCaptchaProcessor->generateChallenge($options['difficulty'], $stamp);

        $challengeFieldOptions = [
            'label'      => $challenge['user_challenge'],
            'label_attr' => [
                'class' => 'math-captcha-challenge-label'
            ],
        ];

        if ($challenge['hash'] === null) {
            $challengeFieldOptions['attr']['disabled'] = true;
            $challengeFieldOptions['label'] = 'No encryption secret found. cannot create challenge.';
        }

        $builder->add('challenge', TextType::class, $challengeFieldOptions);
        $builder->add('hash', HiddenType::class, ['data' => $challenge['hash']]);
        $builder->add('stamp', HiddenType::class, ['data' => $stamp]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['class'] = 'math-captcha';
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_math_captcha_type';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped'      => false,
            'difficulty'  => 'easy',
            'constraints' => [new MathCaptcha()],
        ]);
    }
}
