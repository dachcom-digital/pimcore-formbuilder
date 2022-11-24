<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\FunnelActionsCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\LocalizedValuesCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\OutputWorkflowChannelChoiceType;
use FormBuilderBundle\Model\OutputWorkflowChannel;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Uuid;

class OutputWorkflowChannelType extends AbstractType
{
    protected OutputWorkflowChannelRegistry $channelRegistry;

    public function __construct(OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->channelRegistry = $channelRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', OutputWorkflowChannelChoiceType::class, []);
        $builder->add('name', TextType::class, []);
        $builder->add('funnelActions', FunnelActionsCollectionType::class, []);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $data = $event->getData();
            $form = $event->getForm();
            $formData = $form->getData();

            if (!isset($data['type'])) {
                return;
            }

            if (!$this->channelRegistry->has($data['type'])) {
                return;
            }

            // reset old form data to avoid merging old channel data.
            if ($formData instanceof OutputWorkflowChannel) {
                $formData->setConfiguration([]);
                $formData->setFunnelActions([]);
                $form->setData($formData);
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

            if (!array_key_exists('name', $data)) {

                $name = $formData instanceof OutputWorkflowChannel && !empty($formData->getName())
                    ? $formData->getName()
                    : Uuid::v1()->toRfc4122();

                $event->setData(array_merge($data, ['name' => $name]));
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OutputWorkflowChannel::class,
            'property_path' => '[2]'
        ]);
    }
}
