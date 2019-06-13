<?php

namespace FormBuilderBundle\Twig\Extension;

use FormBuilderBundle\Session\FlashBagManager;

class FlashMessageExtension extends \Twig_Extension
{
    /**
     * @var FlashBagManager
     */
    protected $flashBagManager;

    /**
     * RequestListener constructor.
     *
     * @param FlashBagManager $flashBagManager
     */
    public function __construct(FlashBagManager $flashBagManager)
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
