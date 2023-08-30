<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type\ChannelActionType;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChannelAction implements FunnelActionInterface
{
    protected UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function getName(): string
    {
        return 'Go To Channel';
    }

    public function getFormType(): string
    {
        return ChannelActionType::class;
    }

    public function buildFunnelActionElement(
        FunnelActionElement $funnelActionElement,
        OutputWorkflowChannelInterface $channel,
        array $configuration,
        array $context,
    ): FunnelActionElement {

        $storageToken = $context['storageToken'];

        if ($storageToken === null) {

            $funnelActionElement->setPath('#');

            return $funnelActionElement;
        }

        if (!array_key_exists('channelName', $configuration)) {

            $funnelActionElement->setPath('#');

            return $funnelActionElement;
        }

        $subject = null;
        $verifiedChannelId = null;
        foreach ($channel->getOutputWorkflow()->getChannels() as $availableChannel) {
            if ($availableChannel->getName() === $configuration['channelName']) {
                $verifiedChannelId = $availableChannel->getName();
                $subject = $availableChannel;
                break;
            }
        }

        if ($subject === null) {

            $funnelActionElement->setPath('#');

            return $funnelActionElement;
        }

        $path = $this->router->generate(
            'form_builder.controller.funnel.dispatch',
            [
                'funnelId'     => $channel->getOutputWorkflow()->getId(),
                'channelId'    => $verifiedChannelId,
                'storageToken' => $storageToken
            ]);

        $funnelActionElement->setPath($path);
        $funnelActionElement->setSubject($subject);

        return $funnelActionElement;
    }
}
