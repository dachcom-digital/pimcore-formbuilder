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

use Doctrine\Common\Collections\Collection;

interface OutputWorkflowInterface
{
    public function getId(): int;

    public function setName(string $name): void;

    public function getName(): string;

    public function setFunnelWorkflow(bool $funnelWorkflow): void;

    public function getFunnelWorkflow(): bool;

    public function isFunnelWorkflow(): bool;

    public function setSuccessManagement(array $successManagement): void;

    public function getSuccessManagement(): ?array;

    public function setFormDefinition(FormDefinitionInterface $formDefinition): void;

    public function getFormDefinition(): FormDefinitionInterface;

    public function hasChannels(): bool;

    public function hasChannel(OutputWorkflowChannelInterface $channel): bool;

    public function addChannel(OutputWorkflowChannelInterface $channel): void;

    public function removeChannel(OutputWorkflowChannelInterface $channel): void;

    /**
     * @return Collection<int, OutputWorkflowChannelInterface>
     */
    public function getChannels(): Collection;

    public function getChannelByName(string $name): ?OutputWorkflowChannelInterface;
}
