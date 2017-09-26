<?php

namespace FormBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'path'      => NULL,
            'href_type' => NULL,
            'mapped'    => FALSE,
            'label'     => FALSE,
            'required'  => FALSE
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $vars = array_merge_recursive($view->vars, [
            'data' => '',
            'attr' => [
                'class' => 'form-builder-snippet-element'
            ],
            'path' => $options['path']
        ]);

        $vars['attr']['class'] = join(' ', (array)$vars['attr']['class']);
        $view->vars = $vars;
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
        return 'form_builder_snippet_type';
    }
}