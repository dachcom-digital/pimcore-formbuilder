<?php

namespace FormBuilderBundle\Controller;

use Pimcore\Controller\FrontendController;

class EmailController extends FrontendController
{
    public function emailAction()
    {
        return $this->renderTemplate('@FormBuilder/Email/email.html.twig');
    }
}
