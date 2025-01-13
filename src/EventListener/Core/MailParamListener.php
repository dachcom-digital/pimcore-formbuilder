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
