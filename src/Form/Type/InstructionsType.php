<?php

namespace FormBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstructionsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'        => false,
            'mapped'       => false,
            'required'     => false,
            'instructions' => null
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $vars = array_merge_recursive($view->vars, [
            'instructions' => $options['instructions'] ?? null,
            'attr'         => [
                'data-field-name' => $view->vars['name'],
                'data-field-id'   => $view->vars['id'],
                'class'           => 'form-builder-instruction-element'
            ]
        ]);

        $vars['attr']['class'] = implode(' ', (array) $vars['attr']['class']);

        $view->vars = $vars;
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_instruction_type';
    }
}
