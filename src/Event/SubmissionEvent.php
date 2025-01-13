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

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Model\DoubleOptInSessionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SubmissionEvent extends BaseSubmissionEvent
{
    protected ?array $formRuntimeData;
    protected ?array $funnelRuntimeData;
    protected ?string $redirectUri = null;
    protected bool $outputWorkflowFinisherDisabled = false;
    protected ?DoubleOptInSessionInterface $doubleOptInSession = null;

    public function __construct(
        Request $request,
        ?array $formRuntimeData,
        FormInterface $form,
        ?array $funnelRuntimeData = null,
        bool $useFlashBag = true,
        array $messages = []
    ) {
        parent::__construct(
            $request,
            $form,
            $useFlashBag,
            $messages
        );

        $this->formRuntimeData = $formRuntimeData;
        $this->funnelRuntimeData = $funnelRuntimeData;
    }

    public function disableOutputWorkflowFinisher(): void
    {
        $this->outputWorkflowFinisherDisabled = true;
    }

    public function outputWorkflowFinisherIsDisabled(): bool
    {
        return $this->outputWorkflowFinisherDisabled === true;
    }

    public function setRedirectUri(?string $uri): void
    {
        $this->redirectUri = $uri;
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function hasRedirectUri(): bool
    {
        return !is_null($this->redirectUri);
    }

    public function getFormRuntimeData(): ?array
    {
        return $this->formRuntimeData;
    }

    public function getFunnelRuntimeData(): ?array
    {
        return $this->funnelRuntimeData;
    }

    public function hasDoubleOptInSession(): bool
    {
        return $this->doubleOptInSession instanceof DoubleOptInSessionInterface;
    }

    public function getDoubleOptInSession(): ?DoubleOptInSessionInterface
    {
        return $this->doubleOptInSession;
    }

    public function setDoubleOptInSession(?DoubleOptInSessionInterface $doubleOptInSession): void
    {
        $this->doubleOptInSession = $doubleOptInSession;
    }
}
