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

namespace FormBuilderBundle\Model;

class FunnelActionElement
{
    protected string $path;
    protected mixed $subject = null;
    protected bool $isDisabled = false;

    public function __construct(
        protected FunnelActionDefinition $funnelActionDefinition,
        protected array $coreConfiguration
    ) {
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSubject(): mixed
    {
        return $this->subject;
    }

    public function setSubject(mixed $subject): void
    {
        $this->subject = $subject;
    }

    public function isChannelAware(): bool
    {
        return $this->subject instanceof OutputWorkflowChannelInterface;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->isDisabled = $disabled;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled === true;
    }

    public function ignoreInvalidSubmission(): bool
    {
        if (!array_key_exists('ignoreInvalidFormSubmission', $this->coreConfiguration)) {
            return false;
        }

        return $this->coreConfiguration['ignoreInvalidFormSubmission'] === true;
    }

    public function getFunnelActionDefinition(): FunnelActionDefinition
    {
        return $this->funnelActionDefinition;
    }
}
