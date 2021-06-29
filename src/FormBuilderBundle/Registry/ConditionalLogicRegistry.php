<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Action\ActionInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition\ConditionInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\RuleInterface;

class ConditionalLogicRegistry
{
    protected array $services = [
        'action'    => [],
        'condition' => []
    ];

    protected array $serviceConfiguration = [
        'action'    => [],
        'condition' => []
    ];

    private string $actionInterface;
    private string $conditionInterface;

    public function __construct($actionInterface, $conditionInterface)
    {
        $this->actionInterface = $actionInterface;
        $this->conditionInterface = $conditionInterface;
    }

    public function register(string $identifier, ?RuleInterface $service, ?string $type = null, array $configuration = []): void
    {
        $allowedTypes = ['action', 'condition'];
        if (!is_null($type) && !in_array($type, $allowedTypes)) {
            throw new \InvalidArgumentException(
                sprintf('%s must be a type of %s, "%s" given.', $identifier, implode(' or ', $allowedTypes), $type)
            );
        }

        if (!is_null($service)) {
            $interfaceReference = $type . 'Interface';
            if (!in_array($this->{$interfaceReference}, class_implements($service), true)) {
                throw new \InvalidArgumentException(
                    sprintf('%s needs to implement "%s", "%s" given.', get_class($service), $this->{$interfaceReference}, implode(', ', class_implements($service)))
                );
            }
        }

        $this->services[$type][$identifier] = $service;
        $this->serviceConfiguration[$type][$identifier] = $configuration;
    }

    public function has(string $identifier, string $type): bool
    {
        return isset($this->services[$type]) && isset($this->services[$type][$identifier]);
    }

    /**
     * @return ConditionInterface|ActionInterface
     */
    public function get(string $identifier, string $type): RuleInterface
    {
        if (!$this->has($identifier, $type)) {
            throw new \Exception('"' . $identifier . '" validation service of type "' . $type . '" does not exist.');
        }

        return $this->services[$type][$identifier];
    }

    public function hasCondition(string $identifier): bool
    {
        return $this->has($identifier, 'condition');
    }

    public function getCondition(string $identifier): ConditionInterface
    {
        return $this->get($identifier, 'condition');
    }

    public function hasAction(string $identifier): bool
    {
        return $this->has($identifier, 'action');
    }

    public function getAction(string $identifier): ActionInterface
    {
        return $this->get($identifier, 'action');
    }

    public function getAllConfiguration(string $type): array
    {
        return $this->serviceConfiguration[$type];
    }
}
