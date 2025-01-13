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

namespace FormBuilderBundle\Form\Type\Container;

use FormBuilderBundle\Form\Type\ContainerCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContainerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type'                => ContainerCollectionType::class,
            'required'                  => true,
            'formbuilder_configuration' => [],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset($options['formbuilder_configuration']['template']) && !empty($options['formbuilder_configuration']['template'])) {
            $dataTemplates = [
                $options['formbuilder_configuration']['template']
            ];

            if (isset($view->vars['attr']['data-template'])) {
                $dataTemplates[] = $view->vars['attr']['data-template'];
            }

            $view->vars['attr']['data-template'] = join(' ', $dataTemplates);
        }

        // prevent rendering required class on container root layer
        $view->vars['required'] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'form_builder_container';
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
