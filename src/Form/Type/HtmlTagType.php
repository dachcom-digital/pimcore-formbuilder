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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlTagType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'tag'      => 'label',
            'mapped'   => false,
            'label'    => false,
            'required' => false
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $vars = array_merge_recursive($view->vars, [
            'data' => '',
            'attr' => [
                'data-field-name' => $view->vars['name'],
                'data-field-id'   => $view->vars['id'],
                'class'           => 'form-builder-html-tag-element'
            ],
            'tag'  => empty($options['tag']) ? 'label' : $options['tag']
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
        return 'form_builder_html_tag_type';
    }
}
