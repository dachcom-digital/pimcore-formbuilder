<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\LocalizedValuesCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\OutputWorkflowChannelChoiceType;
use FormBuilderBundle\Model\OutputWorkflowChannel;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowChannelType extends AbstractType
{
    /**
     * @var OutputWorkflowChannelRegistry
     */
    protected $channelRegistry;

    /**
     * @param OutputWorkflowChannelRegistry $channelRegistry
     */
    public function __construct(OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->channelRegistry = $channelRegistry;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', OutputWorkflowChannelChoiceType::class, []);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['type'])) {
                return;
            }

            if (!$this->channelRegistry->has($data['type'])) {
                return;
            }

            $formOptions = [];
            $channel = $this->channelRegistry->get($data['type']);

            if ($channel->isLocalizedConfiguration() === true) {
                $formClass = LocalizedValuesCollectionType::class;
                $formOptions['entry_type'] = $channel->getFormType();
            } else {
                $formClass = $channel->getFormType();
            }

            $form->add('configuration', $formClass, $formOptions);
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OutputWorkflowChannel::class
        ]);
    }
}
