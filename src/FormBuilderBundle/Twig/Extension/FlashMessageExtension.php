<?php

namespace FormBuilderBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FlashMessageExtension extends \Twig_Extension
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * RequestListener constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
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
     * @param       $formId
     * @param array $types
     * @return array
     */
    public function getFlashMessagesForForm($formId, $types = ['success', 'error'])
    {
        $messages = [];
        foreach ($types as $type) {
            $messages[$type] = [];
            $messageKey = $formId . '_' . $type;

            if (!$this->getFlashBag()->has($messageKey)) {
                continue;
            }

            foreach ($this->getFlashBag()->get($messageKey) as $message) {
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
        if (!$this->getFlashBag()->has('formbuilder_redirect_flash_message')) {
            return [];
        }

        return $this->getFlashBag()->get('formbuilder_redirect_flash_message');
    }

    /**
     * @return FlashBagInterface
     */
    private function getFlashBag()
    {
        return $this->session->getFlashBag();
    }
}