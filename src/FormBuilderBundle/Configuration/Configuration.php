<?php

namespace FormBuilderBundle\Configuration;

class Configuration
{
    public const INVALID_FIELD_NAMES = [
        'name',
        'date',
        'inputusername',
        'formid',
        'abstract',
        'class',
        'data',
        'folder',
        'list',
        'permissions',
        'resource',
        'concrete',
        'interface',
        'service',
        'fieldcollection',
        'localizedfield',
        'objectbrick'
    ];

    private array $config;
    private array $backendConfig;

    public function setConfig(array $config = []): void
    {
        $this->backendConfig = [
            'backend_base_field_type_groups' => $config['backend_base_field_type_groups'],
            'backend_base_field_type_config' => $config['backend_base_field_type_config'],
            // backend_field_type_config: not implemented yet.
            'backend_field_type_config'      => $config['backend_field_type_config'] ?? []
        ];

        unset($config['backend_base_field_type_groups'], $config['backend_base_field_type_config'], $config['backend_field_type_config']);

        $this->config = $config;
    }

    public function getConfigArray(): array
    {
        return $this->config;
    }

    public function getBackendConfigArray(): array
    {
        return $this->backendConfig;
    }

    public function getConfig(string $slot): mixed
    {
        return $this->config[$slot];
    }

    public function getConfigFlag(string $flag): bool
    {
        return $this->config['flags'][$flag];
    }

    public function getContainerFieldClass(string $containerName): mixed
    {
        $containerTypes = $this->config['container_types'];

        return $containerTypes[$containerName]['class'];
    }

    public function getAvailableContainer(): array
    {
        $containerTypes = $this->config['container_types'];

        $containerData = [];
        foreach ($containerTypes as $containerId => &$container) {
            if ($container['enabled'] === false) {
                continue;
            }

            $container['id'] = $containerId;
            $containerData[$containerId] = $container;
        }

        return $containerData;
    }

    public function getAvailableConstraints(): array
    {
        $constraints = $this->config['validation_constraints'];

        $constraintData = [];
        $invalidProperties = ['payload'];

        foreach ($constraints as $constraintId => &$constraint) {
            $constraint['id'] = $constraintId;
            $constraintClass = $constraint['class'];

            try {
                $refClass = new \ReflectionClass($constraintClass);
            } catch (\Exception $e) {
                continue;
            }

            $constraintParameters = $refClass->getConstructor()?->getParameters();
            $defaultProperties = $refClass->getDefaultProperties();
            $constraintConfig = [];

            foreach ($refClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $refProperty) {

                $propertyName = $refProperty->getName();

                if (in_array($propertyName, $invalidProperties)) {
                    continue;
                }

                $constructorParameters = array_values(array_filter($constraintParameters ?? [], static function (\ReflectionParameter $parameter) use ($propertyName) {
                    return $parameter->getName() === $propertyName;
                }));

                if (count($constructorParameters) === 0) {
                    continue;
                }

                /** @var \ReflectionParameter $constructorParameter */
                $constructorParameter = $constructorParameters[0];
                $constructorParameterType = null;

                if ($constructorParameter->hasType() && $constructorParameter->getType() instanceof \ReflectionNamedType) {
                    $constructorParameterType = $constructorParameter->getType()->getName();
                }

                if ($constructorParameterType !== null) {
                    $defaultValue = $defaultProperties[$propertyName] ?? null;
                    $type = $constructorParameterType;
                } elseif (isset($defaultProperties[$propertyName])) {
                    $defaultValue = $defaultProperties[$propertyName];
                    $type = gettype($defaultValue);
                } else {
                    $defaultValue = null;
                    $type = 'string';
                }

                if ($defaultValue === null || in_array(gettype($defaultValue), ['string', 'boolean', 'bool', 'integer', 'int', 'array'])) {
                    $constraintConfig[] = [
                        'name'         => $propertyName,
                        'type'         => $type,
                        'defaultValue' => $defaultValue
                    ];
                }
            }

            $constraint['config'] = $constraintConfig;
            $constraintData[$constraintId] = $constraint;
        }

        return $constraintData;
    }

    public function getFieldTypeConfig(string $type): mixed
    {
        return $this->config['types'][$type];
    }

    public function getBackendConfig(string $slot): mixed
    {
        return $this->backendConfig[$slot];
    }

    public function getBackendConditionalLogicConfig(): ?array
    {
        return $this->config['conditional_logic'];
    }
}
