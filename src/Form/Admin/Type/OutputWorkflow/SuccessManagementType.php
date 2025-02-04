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

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\LocalizedValuesCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\PimcoreHrefType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuccessManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', TextType::class);
        $builder->add('identifier', TextType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['identifier'])) {
                return;
            }

            // reset old form data to allow conditional switches
            $form->setData(null);

            $this->buildConditionalForm($form, $data['identifier']);
        });
    }

    protected function buildConditionalForm(FormInterface $form, string $identifier): void
    {
        switch ($identifier) {
            case 'snippet':
                $form->add('value', LocalizedValuesCollectionType::class, ['entry_type' => PimcoreHrefType::class]);

                break;
            case 'redirect':
                $form->add('value', LocalizedValuesCollectionType::class, ['entry_type' => PimcoreHrefType::class]);
                $form->add('flashMessage', TextType::class);

                break;
            case 'redirect_external':
                $form->add('value', UrlType::class);

                break;
            default:
                $form->add('value', TextType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
