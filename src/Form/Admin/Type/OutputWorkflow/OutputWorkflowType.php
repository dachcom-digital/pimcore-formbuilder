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

use Doctrine\ORM\PersistentCollection;
use FormBuilderBundle\Model\OutputWorkflow;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);
        $builder->add('successManagement', SuccessManagementType::class);
        $builder->add('channels', OutputWorkflowChannelCollectionType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            /** @var array $submittedData */
            $submittedData = $event->getData();
            /** @var OutputWorkflowInterface $outputWorkflow */
            $outputWorkflow = $event->getForm()->getData();
            /** @var PersistentCollection $arrayCollection */
            $arrayCollection = $outputWorkflow->getChannels();

            $newSortedSubmittedChannels = [];
            $submittedChannels = $submittedData['channels'] ?? [];

            foreach ($submittedChannels as $submitIndex => $channelData) {
                $name = $channelData['name'];
                foreach ($arrayCollection->toArray() as $index => $outputWorkflowChannel) {
                    $outputWorkflowChannelName = $outputWorkflowChannel->getName();
                    if ($name === $outputWorkflowChannelName) {
                        $newSortedSubmittedChannels[$index] = $channelData;
                        unset($submittedChannels[$submitIndex]);

                        break;
                    }
                }
            }

            foreach ($submittedChannels as $rest) {
                $newSortedSubmittedChannels[] = $rest;
            }

            $submittedData['channels'] = $newSortedSubmittedChannels;

            $event->setData($submittedData);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class'      => OutputWorkflow::class
        ]);
    }
}
