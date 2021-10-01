<?php

namespace FormBuilderBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends FrontendController
{
    public function emailAction(): Response
    {
        return $this->render('@FormBuilder/Email/email.html.twig');
    }
}
