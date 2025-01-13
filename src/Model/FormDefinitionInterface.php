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
use FormBuilderBundle\Model\Fragment\SubFieldsAwareInterface;

interface FormDefinitionInterface extends SubFieldsAwareInterface
{
    public const ALLOWED_FORM_KEYS = [
        'action',
        'method',
        'enctype',
        'noValidate',
        'useAjax',
        'doubleOptIn',
        'attributes'
    ];

    public function getId(): ?int;

    public function setName(string $name): void;

    public function getName(): string;

    public function setGroup(string $groupName): void;

    public function getGroup(): ?string;

    public function setCreationDate(\DateTime $date): void;

    public function getCreationDate(): \DateTime;

    public function setModificationDate(\DateTime $date): void;

    public function getModificationDate(): \DateTime;

    public function setModifiedBy(int $userId): void;

    public function getModifiedBy(): int;

    public function setCreatedBy(int $userId): void;

    public function getCreatedBy(): int;

    public function hasOutputWorkflows(): bool;

    public function hasOutputWorkflow(OutputWorkflowInterface $outputWorkflow): bool;

    public function addOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void;

    public function removeOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void;

    /**
     * @return Collection<int, OutputWorkflowInterface>
     */
    public function getOutputWorkflows(): Collection;

    public function setConfiguration(array $configuration): void;

    public function getConfiguration(): array;

    public function getDoubleOptInConfig(): array;

    public function isDoubleOptInActive(): bool;

    public function setConditionalLogic(array $conditionalLogic): void;

    public function getConditionalLogic(): array;

    /**
     * @throws \Exception
     */
    public function addDynamicField(string $name, string $type, array $options = [], array $optional = []): void;

    /**
     * @throws \Exception
     */
    public function removeDynamicField(string $name): void;

    public function setFields(array $fields): void;

    public function getFields(): array;

    public function getField(string $name, bool $deep = false): ?FieldDefinitionInterface;

    public function getFieldType(string $name, bool $deep = false): ?string;

    /**
     * @throws \Exception
     *
     * @internal
     */
    public function getFieldContainer(string $name): ?FormFieldContainerDefinitionInterface;

    /**
     * @return array<int, FormFieldDefinitionInterface>
     *
     * @internal
     */
    public function getFieldsByType(string $type, ?array $fields = null, array $foundFields = []): array;
}
