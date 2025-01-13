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

class OutputWorkflowChannel implements OutputWorkflowChannelInterface
{
    protected int $id;
    protected string $type;
    protected string $name;
    protected array $configuration;
    protected array $funnelActions;
    protected OutputWorkflowInterface $outputWorkflow;

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setFunnelActions(array $funnelActions): void
    {
        $this->funnelActions = $funnelActions;
    }

    public function getFunnelActions(): array
    {
        return $this->funnelActions;
    }

    public function setOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void
    {
        $this->outputWorkflow = $outputWorkflow;
    }

    public function getOutputWorkflow(): OutputWorkflowInterface
    {
        return $this->outputWorkflow;
    }
}
