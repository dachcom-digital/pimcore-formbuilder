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

use FormBuilderBundle\Validator\Constraints\EmailChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class DoubleOptInType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['instruction_note'] = $options['double_opt_in_instruction_note'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['double_opt_in_instruction_note'] !== null) {
            $builder->add('instructionNote', InstructionsType::class, [
                'instructions' => $options['double_opt_in_instruction_note'],
            ]);
        }

        $builder->add('emailAddress', EmailType::class, [
            'label'       => 'form_builder.form.double_opt_in.email',
            'constraints' => [
                new NotBlank(),
                new Email(),
                new EmailChecker()
            ]
        ]);

        if ($options['render_form_id_field']) {
            $builder->add('formId', HiddenType::class, [
                'mapped' => false,
                'data'   => $options['current_form_id'],
            ]);
        }

        if ($options['render_conditional_logic_field']) {
            $builder->add('formCl', HiddenType::class, [
                'mapped' => false,
                'data'   => json_encode([], JSON_THROW_ON_ERROR)
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'form_builder.form.double_opt_in.submit',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_double_opt_in';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current_form_id'                => 0,
            'csrf_protection'                => true,
            'render_conditional_logic_field' => true,
            'render_form_id_field'           => true,
            'is_headless_form'               => false,
            'double_opt_in_instruction_note' => null
        ]);
    }
}
