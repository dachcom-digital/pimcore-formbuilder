<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Registry\FunnelActionRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FunnelActionType extends AbstractType
{
    protected FunnelActionRegistry $funnelActionRegistry;

    public function __construct(FunnelActionRegistry $funnelActionRegistry)
    {
        $this->funnelActionRegistry = $funnelActionRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', TextType::class);
        $builder->add('triggerName', TextType::class);
        $builder->add('coreConfiguration', FunnelActionCoreConfigType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['type'])) {
                return;
            }

            if (!$this->funnelActionRegistry->has($data['type'])) {
                return;
            }

            $funnelLayer = $this->funnelActionRegistry->get($data['type']);

            $form->add('configuration', $funnelLayer->getFormType());
        });
    }
}
