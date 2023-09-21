<?php

namespace FormBuilderBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends FrontendController
{
    public function emailAction(Request $request): Response
    {
        return $this->render('@FormBuilder/email/email.html.twig', [
            'editmode' => $this->editmode,
            'document' => $this->document,
            'body'     => $request->attributes->has('body') ? $request->attributes->get('body') : null
        ]);
    }
}
