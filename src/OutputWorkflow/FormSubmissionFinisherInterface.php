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

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\DoubleOptInSubmissionEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FormSubmissionFinisherInterface
{
    public function finishWithError(Request $request, FormInterface $form): ?Response;

    public function finishWithSuccess(Request $request, SubmissionEvent $submissionEvent): ?Response;

    public function finishDoubleOptInWithSuccess(Request $request, DoubleOptInSubmissionEvent $submissionEvent): ?Response;
}
