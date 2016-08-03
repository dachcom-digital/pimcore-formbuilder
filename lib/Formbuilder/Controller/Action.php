<?php
namespace Members\Controller;

use Pimcore\Controller\Action\Frontend;
use Members\Auth;

class Action extends Frontend
{
    public function init()
    {
        parent::init();

        //allow website path to override templates
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts');
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/layouts');
    }
}