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

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Event\SubmissionEvent;
use Symfony\Component\HttpFoundation\Request;

class FunnelLayerData
{
    protected string $view;
    protected array $arguments = [];

    public function __construct(
        protected Request $request,
        protected SubmissionEvent $submissionEvent,
        protected array $funnelLayerConfiguration
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRootFormSubmissionEvent(): SubmissionEvent
    {
        return $this->submissionEvent;
    }

    public function getFunnelLayerConfiguration(): array
    {
        return $this->funnelLayerConfiguration;
    }

    public function setFunnelLayerView(string $view): void
    {
        $this->view = $view;
    }

    public function getFunnelLayerView(): string
    {
        return $this->view;
    }

    public function setFunnelLayerViewArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getFunnelLayerViewArguments(): array
    {
        return $this->arguments;
    }
}
