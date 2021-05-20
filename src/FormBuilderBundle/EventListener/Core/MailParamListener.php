<?php

namespace FormBuilderBundle\EventListener\Core;

use Pimcore\Event\MailEvents;
use Pimcore\Event\Model\MailEvent;
use Pimcore\Mail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailParamListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            MailEvents::PRE_SEND => ['watchEmailParams'],
        ];
    }

    /**
     * @param MailEvent $event
     */
    public function watchEmailParams(MailEvent $event)
    {
        $mail = $event->getMail();

        if (!$mail instanceof Mail) {
            return;
        }

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
