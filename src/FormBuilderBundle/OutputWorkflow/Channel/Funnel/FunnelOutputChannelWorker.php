<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel;

use FormBuilderBundle\Configuration\Configuration;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

class FunnelOutputChannelWorker
{
    protected Configuration $configuration;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        Configuration $configuration,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->configuration = $configuration;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \Exception
     */
    public function process(FormInterface $form, array $channelConfiguration, array $formRuntimeData, string $workflowName, string $locale): void
    {

    }
}
