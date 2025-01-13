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

namespace FormBuilderBundle\Twig\Extension;

use FormBuilderBundle\Session\FlashBagManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashMessageExtension extends AbstractExtension
{
    public function __construct(protected FlashBagManagerInterface $flashBagManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('form_builder_get_flash_messages', [$this, 'getFlashMessagesForForm']),
            new TwigFunction('form_builder_get_redirect_flash_messages', [$this, 'getFlashMessagesForRedirectForm'])
        ];
    }

    public function getFlashMessagesForForm(string|int $formId, array $types = ['success', 'error']): array
    {
        $messages = [];
        foreach ($types as $type) {
            $messages[$type] = [];
            $messageKey = sprintf('%s_%s', $formId, $type);

            if (!$this->flashBagManager->has($messageKey)) {
                continue;
            }

            foreach ($this->flashBagManager->get($messageKey) as $message) {
                $messages[$type][] = $message;
            }
        }

        return $messages;
    }

    public function getFlashMessagesForRedirectForm(): array
    {
        if (!$this->flashBagManager->has('formbuilder_redirect_flash_message')) {
            return [];
        }

        return $this->flashBagManager->get('formbuilder_redirect_flash_message');
    }
}
