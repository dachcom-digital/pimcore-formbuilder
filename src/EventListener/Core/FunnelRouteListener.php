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

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\EventSubscriber\SignalSubscribeHandler;
use FormBuilderBundle\Resolver\FunnelDataResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FunnelRouteListener implements EventSubscriberInterface
{
    public function __construct(
        protected FunnelDataResolver $funnelDataResolver,
        protected SignalSubscribeHandler $signalSubscribeHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST   => ['setupFunnelRequest'],
            KernelEvents::EXCEPTION => ['setupFunnelExceptionRequest'],
            KernelEvents::RESPONSE  => ['shutdownFunnelRequest'],
        ];
    }

    public function setupFunnelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->funnelDataResolver->isFunnelProcessRequest($event->getRequest())) {
            return;
        }

        try {
            $this->funnelDataResolver->buildFunnelData($event->getRequest());
        } catch (\Throwable $e) {
            return;
        }

        $this->signalSubscribeHandler->listen(
            SignalSubscribeHandler::CHANNEL_FUNNEL_PROCESS,
            [
                'funnelData' => $this->funnelDataResolver->getFunnelData($event->getRequest())
            ]
        );
    }

    public function setupFunnelExceptionRequest(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->funnelDataResolver->isFunnelProcessRequest($event->getRequest())) {
            return;
        }

        $this->signalSubscribeHandler->listen(
            SignalSubscribeHandler::CHANNEL_FUNNEL_PROCESS,
            [
                'funnelData' => $this->funnelDataResolver->getFunnelData($event->getRequest())
            ]
        );

        // instant broadcasting
        $this->signalSubscribeHandler->broadcast(['exception' => $event->getThrowable()]);
    }

    public function shutdownFunnelRequest(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->funnelDataResolver->isFunnelProcessRequest($event->getRequest())) {
            return;
        }

        $isFunnelShutdownRequest = $this->funnelDataResolver->isFunnelShutdownRequest($event->getRequest());

        $this->signalSubscribeHandler->broadcast(['funnel_shutdown' => $isFunnelShutdownRequest]);

        if ($isFunnelShutdownRequest === true) {
            $this->funnelDataResolver->flushFunnelData($event->getRequest());
        }
    }
}
