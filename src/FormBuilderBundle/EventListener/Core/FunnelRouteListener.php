<?php

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
    protected FunnelDataResolver $funnelDataResolver;
    protected SignalSubscribeHandler $signalSubscribeHandler;

    public function __construct(
        FunnelDataResolver $funnelDataResolver,
        SignalSubscribeHandler $signalSubscribeHandler,
    ) {
        $this->funnelDataResolver = $funnelDataResolver;
        $this->signalSubscribeHandler = $signalSubscribeHandler;
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
            SignalSubscribeHandler::CHANNEL_FUNNEL_PROCESS, [
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
            SignalSubscribeHandler::CHANNEL_FUNNEL_PROCESS, [
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

        $this->signalSubscribeHandler->broadcast();

        if ($this->funnelDataResolver->isFunnelShutdownRequest($event->getRequest())) {
            $this->funnelDataResolver->flushFunnelData($event->getRequest());
        }
    }
}
