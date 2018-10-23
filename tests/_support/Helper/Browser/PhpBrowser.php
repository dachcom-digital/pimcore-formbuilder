<?php

namespace DachcomBundle\Test\Helper\Browser;

use Codeception\Module;
use Codeception\Lib;
use Codeception\Exception\ModuleException;
use DachcomBundle\Test\Helper\PimcoreCore;
use DachcomBundle\Test\Helper\PimcoreUser;
use DachcomBundle\Test\Util\FormHelper;
use Pimcore\Model\User;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

class PhpBrowser extends Module implements Lib\Interfaces\DependsOnModule
{
    const PIMCORE_ADMIN_CSRF_TOKEN_NAME = 'MOCK_CSRF_TOKEN';

    /**
     * @var Cookie
     */
    protected $sessionSnapShot;

    /**
     * @var PimcoreCore
     */
    protected $pimcoreCore;

    /**
     * @return array|mixed
     */
    public function _depends()
    {
        return [
            'Codeception\Module\Symfony' => 'PhpBrowser needs the pimcore core framework to work.'
        ];
    }

    /**
     * @param PimcoreCore $pimcoreCore
     */
    public function _inject($pimcoreCore)
    {
        $this->pimcoreCore = $pimcoreCore;
    }

    /**
     * @inheritDoc
     */
    public function _initialize()
    {
        $this->sessionSnapShot = [];

        parent::_initialize();
    }

    /**
     * Actor Function to see a page with enabled edit-mode
     *
     * @param string $page
     */
    public function amOnPageInEditMode(string $page)
    {
        $this->pimcoreCore->amOnPage(sprintf('%s?pimcore_editmode=true', $page));
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param null   $data
     * @param null   $selector
     */
    public function seeAEditableConfiguration(string $name, string $type, array $options, $data = null, $selector = null)
    {
        $this->pimcoreCore->see(FormHelper::generateEditableConfiguration($name, $type, $options, $data), $selector);
    }

    /**
     * Actor Function to login into Pimcore Backend
     *
     * @param $username
     */
    public function amLoggedInAs($username)
    {
        $firewallName = 'admin';

        try {
            /** @var PimcoreUser $userModule */
            $userModule = $this->getModule('\\' . PimcoreUser::class);
        } catch (ModuleException $pimcoreModule) {
            $this->debug('[PIMCORE BUNDLE MODULE] could not load pimcore user module');
            return;
        }

        $pimcoreUser = $userModule->getUser($username);

        if (!$pimcoreUser instanceof User) {
            $this->debug(sprintf('[PIMCORE BUNDLE MODULE] could not fetch user %s.', $username));
            return;
        }

        /** @var Session $session */
        $session = $this->pimcoreCore->getContainer()->get('session');

        $this->pimcoreCore->client->getCookieJar()->clear();

        $user = new \Pimcore\Bundle\AdminBundle\Security\User\User($pimcoreUser);
        $token = new UsernamePasswordToken($user, null, $firewallName, $pimcoreUser->getRoles());
        $this->pimcoreCore->getContainer()->get('security.token_storage')->setToken($token);

        \Pimcore\Tool\Session::useSession(function (AttributeBagInterface $adminSession) use ($pimcoreUser) {
            \Pimcore\Tool\Session::regenerateId();
            $adminSession->set('user', $pimcoreUser);
            $adminSession->set('csrfToken', self::PIMCORE_ADMIN_CSRF_TOKEN_NAME);
        });

        // allow re-usage of session in same cest.
        if (!empty($this->sessionSnapShot)) {
            $cookie = $this->sessionSnapShot;
        } else {
            $cookie = new Cookie($session->getName(), $session->getId());
            $this->sessionSnapShot = $cookie;
        }

        $this->pimcoreCore->client->getCookieJar()->set($cookie);

    }

    /**
     * Actor Function to send tokenized ajax request in backend
     *
     * @param string $url
     * @param array  $params
     */
    public function sendTokenAjaxPostRequest(string $url, array $params = [])
    {
        $params['csrfToken'] = self::PIMCORE_ADMIN_CSRF_TOKEN_NAME;
        $this->pimcoreCore->sendAjaxPostRequest($url, $params);
    }
}
