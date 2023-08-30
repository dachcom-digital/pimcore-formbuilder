<?php

namespace FormBuilderBundle\EventListener\Core;

use Pimcore\Event\MailEvents;
use Pimcore\Event\Model\MailEvent;
use Pimcore\Mail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailParamListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MailEvents::PRE_SEND => ['watchEmailParams'],
        ];
    }

    public function watchEmailParams(MailEvent $event): void
    {
        $mail = $event->getMail();

        // mail does not have nice way to check existing params
        $params = $mail->getParams();

        if (!isset($params['_form_builder_id'])) {
            return;
        }

        if (!isset($params['body'])) {
            return;
        }

        // remove rendered body property
        $mail->unsetParam('body');
    }
}
