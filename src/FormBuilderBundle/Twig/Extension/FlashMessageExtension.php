<?php

namespace FormBuilderBundle\Twig\Extension;

use FormBuilderBundle\Session\FlashBagManagerInterface;

class FlashMessageExtension extends \Twig_Extension
{
    /**
     * @var FlashBagManagerInterface
     */
    protected $flashBagManager;

    /**
     * RequestListener constructor.
     *
     * @param FlashBagManagerInterface $flashBagManager
     */
    public function __construct(FlashBagManagerInterface $flashBagManager)
    {
        $this->flashBagManager = $flashBagManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('form_builder_get_flash_messages', [$this, 'getFlashMessagesForForm']),
            new \Twig_Function('form_builder_get_redirect_flash_messages', [$this, 'getFlashMessagesForRedirectForm'])
        ];
    }

    /**
     * @param int   $formId
     * @param array $types
     *
     * @return array
     */
    public function getFlashMessagesForForm($formId, $types = ['success', 'error'])
    {
        $messages = [];
        foreach ($types as $type) {
            $messages[$type] = [];
            $messageKey = $formId . '_' . $type;

            if (!$this->flashBagManager->has($messageKey)) {
                continue;
            }

            foreach ($this->flashBagManager->get($messageKey) as $message) {
                $messages[$type][] = $message;
            }
        }

        return $messages;
    }

    /**
     * @return array
     */
    public function getFlashMessagesForRedirectForm()
    {
        if (!$this->flashBagManager->has('formbuilder_redirect_flash_message')) {
            return [];
        }

        return $this->flashBagManager->get('formbuilder_redirect_flash_message');
    }
}
