<?php

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
            $messageKey = sprintf('formbuilder_%d_%s', $formId, $type);

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
