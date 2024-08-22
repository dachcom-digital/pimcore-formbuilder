<?php

namespace FormBuilderBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FormBuilderBundle\Configuration\Configuration;

class FormDefinition implements FormDefinitionInterface
{
    protected ?int $id = null;
    protected string $name;
    protected ?string $group = null;
    protected \DateTime $creationDate;
    protected \DateTime $modificationDate;
    protected int $modifiedBy;
    protected int $createdBy;
    protected Collection $outputWorkflows;
    public array $configuration = [];
    public array $conditionalLogic = [];
    public array $fields = [];

    public function __construct()
    {
        $this->outputWorkflows = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return is_numeric($this->id) ? (int) $this->id : null;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setGroup(?string $groupName = null): void
    {
        $this->group = !empty($groupName) && is_string($groupName) ? $groupName : null;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setCreationDate(\DateTime $date): void
    {
        $this->creationDate = $date;
    }

    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    public function setModificationDate(\DateTime $date): void
    {
        $this->modificationDate = $date;
    }

    public function getModificationDate(): \DateTime
    {
        return $this->modificationDate;
    }

    public function setModifiedBy(int $userId): void
    {
        $this->modifiedBy = $userId;
    }

    public function getModifiedBy(): int
    {
        return (int) $this->modifiedBy;
    }

    public function setCreatedBy(int $userId): void
    {
        $this->createdBy = $userId;
    }

    public function getCreatedBy(): int
    {
        return (int) $this->createdBy;
    }

    public function hasOutputWorkflows(): bool
    {
        return !$this->outputWorkflows->isEmpty();
    }

    public function hasOutputWorkflow(OutputWorkflowInterface $outputWorkflow): bool
    {
        return $this->outputWorkflows->contains($outputWorkflow);
    }

    public function addOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void
    {
        if (!$this->hasOutputWorkflow($outputWorkflow)) {
            $this->outputWorkflows->add($outputWorkflow);
            $outputWorkflow->setFormDefinition($this);
        }
    }

    public function removeOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void
    {
        if ($this->hasOutputWorkflow($outputWorkflow)) {
            $this->outputWorkflows->removeElement($outputWorkflow);
            $outputWorkflow->setFormDefinition(null);
        }
    }

    public function getOutputWorkflows(): Collection
    {
        return $this->outputWorkflows;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = array_intersect_key($configuration, array_flip(self::ALLOWED_FORM_KEYS));
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getDoubleOptInConfig(): array
    {
        return $this->configuration['doubleOptIn'] ?? [];
    }

    public function isDoubleOptInActive(): bool
    {
        $doubleOptInConfig = $this->getDoubleOptInConfig();

        return ($doubleOptInConfig['enabled'] ?? false) === true;
    }

    public function getConditionalLogic(): array
    {
        return $this->conditionalLogic;
    }

    public function setConditionalLogic(array $conditionalLogic): void
    {
        $this->conditionalLogic = $conditionalLogic;
    }

    public function addDynamicField(string $name, string $type, array $options = [], array $optional = []): void
    {
        if (in_array($name, Configuration::INVALID_FIELD_NAMES)) {
            throw new \Exception(sprintf('\'%s\' is a reserved form field name used by the form builder bundle and cannot be used.', $name));
        }

        if (isset($this->fields[$name])) {
            if (!$this->fields[$name] instanceof FormFieldDynamicDefinitionInterface) {
                throw new \Exception(sprintf('"%s" as field name is already used by form builder fields.', $name));
            }
        }

        $dynamicField = new FormFieldDynamicDefinition($name, $type, $options, $optional);
        $this->fields[$name] = $dynamicField;
    }

    public function removeDynamicField(string $name): void
    {
        if (!isset($this->fields[$name])) {
            throw new \Exception(sprintf('cannot remove dynamic field, "%s" does not exists', $name));
        }

        if (isset($this->fields[$name]) && $this->fields[$name] instanceof FormFieldDynamicDefinitionInterface) {
            unset($this->fields[$name]);
        }
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $name, bool $deep = false): ?FieldDefinitionInterface
    {
        return $this->findField($this->getFields(), $name, $deep);
    }

    public function getFieldType(string $name, bool $deep = false): ?string
    {
        $field = $this->findField($this->getFields(), $name, $deep);

        if ($field === null) {
            return null;
        }

        return $field->getType();
    }

    public function getFieldContainer(string $name): ?FormFieldContainerDefinitionInterface
    {
        $fieldContainerDefinition = $this->getField($name);
        if ($fieldContainerDefinition !== null && !$fieldContainerDefinition instanceof FormFieldContainerDefinitionInterface) {
            throw new \Exception(sprintf('Requested field "%s" container is not an instance of FormFieldContainerDefinitionInterface', $name));
        }

        return $fieldContainerDefinition;
    }

    public function getFieldsByType(string $type, ?array $fields = null, array $foundFields = []): array
    {
        if ($fields === null) {
            $fields = $this->getFields();
        }

        foreach ($fields as $field) {
            if ($field->getType() === $type) {
                $foundFields[] = $field;
            }

            if ($field instanceof FormFieldContainerDefinitionInterface && count($field->getFields()) > 0) {
                $foundFields = $this->getFieldsByType($type, $field->getFields(), $foundFields);
            }
        }

        return $foundFields;
    }

    protected function findField(array $fields, mixed $value, bool $deep = false): ?FieldDefinitionInterface
    {
        foreach ($fields as $field) {
            if ($field->getName() === $value) {
                return $field;
            }

            if ($deep === true && $field instanceof FormFieldContainerDefinitionInterface) {
                $subField = $this->findField($field->getFields(), $value, $deep);
                if ($subField !== null) {
                    return $subField;
                }
            }
        }

        return null;
    }
}
