<?php

namespace FormBuilderBundle\Session;

use Pimcore\Session\SessionConfiguratorInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionConfigurator implements SessionConfiguratorInterface
{
    /**
     * @inheritDoc
     */
    public function configure(SessionInterface $session)
    {
        $bag = new NamespacedAttributeBag('_form_builder_session');
        $bag->setName('form_builder_session');
        $session->registerBag($bag);
    }
}
