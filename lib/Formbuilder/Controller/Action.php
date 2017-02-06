<?php

namespace Formbuilder\Controller;

use Pimcore\Controller\Action\Frontend;

class Action extends Frontend
{
    public function init()
    {
        //allow website path to override templates
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts');
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/layouts');

        parent::init();
    }
}