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

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\ObjectMappingElementCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\PimcoreHrefType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('resolveStrategy', ChoiceType::class, ['choices' => ['existingObject' => 'existingObject', 'newObject' => 'newObject']]);
        $builder->add('objectMappingData', ObjectMappingElementCollectionType::class, []);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['resolveStrategy'])) {
                return;
            }

            // reset old form data to allow conditional switches
            $form->setData(null);

            $dynamicObjectResolver = array_key_exists('dynamicObjectResolver', $data) && !empty($data['dynamicObjectResolver']);

            if ($dynamicObjectResolver === true) {
                $form->add('dynamicObjectResolver', TextType::class);
                $form->add('dynamicObjectResolverClass', TextType::class);
            } elseif ($data['resolveStrategy'] === 'existingObject') {
                $form->add('resolvingObject', PimcoreHrefType::class);
            } elseif ($data['resolveStrategy'] === 'newObject') {
                $form->add('storagePath', PimcoreHrefType::class);
                $form->add('resolvingObjectClass', TextType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
