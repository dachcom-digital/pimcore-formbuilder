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

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type\ChannelActionType;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChannelAction implements FunnelActionInterface
{
    public function __construct(protected UrlGeneratorInterface $router)
    {
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
            ]
        );

        $funnelActionElement->setPath($path);
        $funnelActionElement->setSubject($subject);

        return $funnelActionElement;
    }
}
