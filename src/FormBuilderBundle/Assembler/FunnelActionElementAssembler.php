<?php

namespace FormBuilderBundle\Assembler;

use FormBuilderBundle\Model\FunnelActionDefinition;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\FunnelActionElementStack;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\VirtualActionDefinitions;
use FormBuilderBundle\OutputWorkflow\Channel\FunnelAwareChannelInterface;
use FormBuilderBundle\Registry\FunnelActionRegistry;
use FormBuilderBundle\Resolver\FunnelDataResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FunnelActionElementAssembler
{
    protected FunnelActionRegistry $funnelActionRegistry;

    public function __construct(FunnelActionRegistry $funnelActionRegistry)
    {
        $this->funnelActionRegistry = $funnelActionRegistry;
    }

    public function assembleItem(
        OutputWorkflowChannelInterface $channel,
        ChannelInterface $channelProcessor,
        array $funnelActionConfiguration,
        array $funnelActionContext,
    ): FunnelActionElement {
        return $this->assembleElement($channel, $channelProcessor, $funnelActionConfiguration, $funnelActionContext);
    }

    public function assembleStack(
        OutputWorkflowChannelInterface $channel,
        ChannelInterface $channelProcessor,
        array $funnelActionContext
    ): FunnelActionElementStack {
        $funnelActionStack = new FunnelActionElementStack();
        foreach ($channel->getFunnelActions() as $funnelActionConfiguration) {
            $funnelActionStack->add($this->assembleElement($channel, $channelProcessor, $funnelActionConfiguration, $funnelActionContext));
        }

        return $funnelActionStack;
    }

    private function assembleElement(
        OutputWorkflowChannelInterface $channel,
        ChannelInterface $channelProcessor,
        array $funnelActionConfiguration,
        array $funnelActionContext,
    ): FunnelActionElement {

        $contextOptionsResolver = new OptionsResolver();
        $contextOptionsResolver->setDefaults([
            'initiationPath' => null,
            'storageToken'   => null,
            'errorToken'     => null,
        ]);

        $contextOptionsResolver->setAllowedTypes('initiationPath', ['string']);
        $contextOptionsResolver->setAllowedTypes('storageToken', ['string']);
        $contextOptionsResolver->setAllowedTypes('errorToken', ['string', 'null']);

        $context = $contextOptionsResolver->resolve($funnelActionContext);

        $channelConfiguration = $channel->getConfiguration();

        if ($channelProcessor instanceof FunnelAwareChannelInterface) {
            $funnelActionDefinition = $this->getFunnelActionDefinition(
                $channelProcessor->getFunnelLayer($channelConfiguration)->getFunnelActionDefinitions(),
                $funnelActionConfiguration['triggerName']
            );
        } else {
            $funnelActionDefinition = $this->getFunnelActionDefinition(
                VirtualActionDefinitions::getVirtualFunnelActionDefinitions(),
                $funnelActionConfiguration['triggerName']
            );
        }

        $funnelAction = $this->funnelActionRegistry->get($funnelActionConfiguration['type']);

        $funnelActionElement = $funnelAction->buildFunnelActionElement(
            new FunnelActionElement($funnelActionDefinition, $funnelActionConfiguration['coreConfiguration'] ?? []),
            $channel,
            $funnelActionConfiguration['configuration'] ?? [],
            $context
        );

        $this->assertFunnelQueryArguments($channel, $funnelActionElement, $context);

        return $funnelActionElement;
    }

    private function getFunnelActionDefinition(array $funnelActionDefinitions, string $name): FunnelActionDefinition
    {
        if ($name === '__INITIATE_FUNNEL') {
            return new FunnelActionDefinition($name, $name, []);
        }

        foreach ($funnelActionDefinitions as $definition) {
            if ($definition->getName() === $name) {
                return $definition;
            }
        }

        throw new \Exception(sprintf('Funnel Action Definition with name "%s" not found', $name));
    }

    private function assertFunnelQueryArguments(OutputWorkflowChannelInterface $channel, FunnelActionElement $funnelActionElement, array $context): void
    {
        if ($funnelActionElement->getPath() === '#') {
            return;
        }

        $funnelFinished = true;
        $outputWorkflow = $channel->getOutputWorkflow();

        if ($funnelActionElement->isChannelAware()) {
            // @todo: no necessarily true, we need to add a flag to the channel to determinate if it's the last one
            // which allows us to shutdown the funnel process!
            $funnelFinished = $this->isLastChannel($outputWorkflow, $funnelActionElement->getSubject());
        }

        $path = $funnelActionElement->getPath();

        $query = [];

        if (!str_contains($path, $context['storageToken'])) {
            $query[FunnelDataResolver::FUNNEL_STORAGE_TOKEN_FRAGMENT] = $context['storageToken'];
        }

        if ($context['errorToken'] !== null) {
            $query[FunnelDataResolver::FUNNEL_ERROR_TOKEN_FRAGMENT] = $context['errorToken'];
        }

        if ($funnelFinished === true) {
            $query[FunnelDataResolver::FUNNEL_FUNNEL_FINISHED_FRAGMENT] = $outputWorkflow->getFormDefinition()->getId();
        }

        if (count($query) === 0) {
            return;
        }

        $path = sprintf('%s%s%s', $path, str_contains($path, '?') ? '&' : '?', http_build_query($query));

        $funnelActionElement->setPath($path);
    }

    private function isLastChannel(OutputWorkflowInterface $outputWorkflow, OutputWorkflowChannelInterface $channel): bool
    {
        if ($outputWorkflow->getChannels()->isEmpty()) {
            return true;
        }

        return $outputWorkflow->getChannels()->last()->getId() === $channel->getId();
    }

}
