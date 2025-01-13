<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
