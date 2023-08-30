<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel;

use FormBuilderBundle\Registry\FunnelLayerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FunnelChannelType extends AbstractType
{
    public function __construct(protected FunnelLayerRegistry $funnelLayerRegistry)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', TextType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['type'])) {
                return;
            }

            if (!$this->funnelLayerRegistry->has($data['type'])) {
                return;
            }

            $funnelLayer = $this->funnelLayerRegistry->get($data['type']);
            $formType = $funnelLayer->getFormType();

            $form->add('configuration', $formType['type'], $formType['options']);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
