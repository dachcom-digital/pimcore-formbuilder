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

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker\FieldCollectionWorkerType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker\RelationWorkerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);
        $builder->add('worker', ChoiceType::class, ['choices' => ['fieldCollectionWorker' => 'fieldCollectionWorker', 'relationWorker' => 'relationWorker']]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['worker'])) {
                $this->addWorker($data['worker'], $form);
            }
        });
    }

    protected function addWorker(string $workerType, FormInterface $form): void
    {
        if ($workerType === 'fieldCollectionWorker') {
            $form->add('workerData', FieldCollectionWorkerType::class);
        } elseif ($workerType === 'relationWorker') {
            $form->add('workerData', RelationWorkerType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'config_type'       => null,
            'field_config_type' => null,
        ]);
    }
}
