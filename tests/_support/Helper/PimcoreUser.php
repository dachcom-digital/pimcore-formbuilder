<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Pimcore\Model\User;
use Pimcore\Tests\Util\TestHelper;
use Pimcore\Tool\Authentication;

class PimcoreUser extends Module
{
    /**
     * @var User[]
     */
    protected $users = [];

    /**
     * @inheritDoc
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        parent::_before($test);
    }

    /**
     * Actor Function to create a User
     *
     * @param $username
     */
    public function haveAUser($username)
    {
        $this->createUser($username, false);
    }

    /**
     * Actor Function to create a Admin User
     *
     * @param $username
     */
    public function haveAUserWithAdminRights($username)
    {
        $this->createUser($username, true);
    }

    /**
     * API Function to get a User
     *
     * @param string $username
     *
     * @return User
     */
    public function getUser($username)
    {
        if (isset($this->users[$username])) {
            return $this->users[$username];
        }

        throw new \InvalidArgumentException(sprintf('User %s does not exist', $username));
    }

    /**
     * API Function to create a User
     *
     * @param string $username
     * @param bool   $admin
     *
     * @return null|User|User\AbstractUser
     */
    protected function createUser($username, $admin = true)
    {
        if (!TestHelper::supportsDbTests()) {
            $this->debug(sprintf('[PIMCORE USER MODULE] Not initializing user %s as DB is not connected', $username));
            return null;
        } else {
            $this->debug(sprintf('[PIMCORE USER MODULE] Initializing user %s', $username));
        }

        $password = $username;

        $user = null;

        try {
            $user = User::getByName($username);
        } catch (\Exception $e) {
            // fail silently
        }

        if ($user instanceof User) {
            return $user;
        }

        $this->debug(sprintf('[PIMCORE USER MODULE] Creating user %s', $username));

        $pass = null;

        try {
            $pass = Authentication::getPasswordHash($username, $password);
        } catch (\Exception $e) {
            // fail silently.
        }

        $user = User::create([
            'parentId' => 0,
            'username' => $username,
            'password' => $pass,
            'active'   => true,
            'admin'    => $admin
        ]);

        $this->users[$user->getName()] = $user;

        return $user;
    }
}
