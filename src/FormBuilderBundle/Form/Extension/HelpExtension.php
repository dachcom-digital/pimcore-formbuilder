<?php

namespace FormBuilderBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HelpExtension extends AbstractTypeExtension
{
    public function getExtendedType(): string
    {
        return FormType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['help_text']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['help_text'] = isset($options['help_text']) ? $options['help_text'] : null;
    }
}
