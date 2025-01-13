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

interface OutputWorkflowChannelInterface
{
    public function getId(): int;

    public function setType(string $type): void;

    public function getType(): string;

    public function getName(): string;

    public function setName(string $name): void;

    public function setConfiguration(array $configuration): void;

    public function getConfiguration(): array;

    public function setFunnelActions(array $funnelActions): void;

    public function getFunnelActions(): array;

    public function setOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void;

    public function getOutputWorkflow(): OutputWorkflowInterface;
}
