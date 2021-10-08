<?php

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

    /**
     * @deprecated since 4.0 and will be removed with 5.0
     */
    public function setMailLayout(?array $mailLayout = null): void;

    /**
     * @deprecated since 4.0 and will be removed with 5.0
     */
    public function getMailLayout(): ?array;

    public function hasOutputWorkflows(): bool;

    public function hasOutputWorkflow(OutputWorkflowInterface $outputWorkflow): bool;

    public function addOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void;

    public function removeOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void;

    /**
     * @return Collection<int, OutputWorkflowInterface>
     */
    public function getOutputWorkflows(): Collection;

    public function setConfig(array $config): void;

    public function getConfig(): array;

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
    public function getFieldsByType(string $type): array;
}
