<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Action\ActionInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition\ConditionInterface;

class ConditionalLogicRegistry
{
    /**
     * @var array
     */
    protected $services = [
        'action'    => [],
        'condition' => []
    ];

    /**
     * @var array
     */
    protected $serviceConfiguration = [
        'action'    => [],
        'condition' => []
    ];

    /**
     * @var string
     */
    private $actionInterface;

    /**
     * @var string
     */
    private $conditionInterface;

    /**
     * ConditionalLogicRegistry constructor.
     *
     * @param string $actionInterface
     * @param string $conditionInterface
     */
    public function __construct($actionInterface, $conditionInterface)
    {
        $this->actionInterface = $actionInterface;
        $this->conditionInterface = $conditionInterface;
    }

    /**
     * @param string                             $identifier
     * @param ActionInterface|ConditionInterface $service
     * @param string                             $type
     * @param array                              $configuration
     */
    public function register($identifier, $service, $type = null, $configuration = [])
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

    /**
     * @param string $identifier
     * @param string $type
     *
     * @return bool
     */
    public function has($identifier, $type)
    {
        return isset($this->services[$type]) && isset($this->services[$type][$identifier]);
    }

    /**
     * @param string $identifier
     * @param string $type
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($identifier, $type)
    {
        if (!$this->has($identifier, $type)) {
            throw new \Exception('"' . $identifier . '" validation service of type "' . $type . '" does not exist.');
        }

        return $this->services[$type][$identifier];
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasCondition($identifier)
    {
        return $this->has($identifier, 'condition');
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getCondition($identifier)
    {
        return $this->get($identifier, 'condition');
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasAction($identifier)
    {
        return $this->has($identifier, 'action');
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getAction($identifier)
    {
        return $this->get($identifier, 'action');
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function getAllConfiguration($type)
    {
        return $this->serviceConfiguration[$type];
    }
}
