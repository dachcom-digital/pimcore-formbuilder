<?php

namespace FormBuilderBundle\Configuration;

use Symfony\Component\Filesystem\Filesystem;

class Configuration
{
    const SYSTEM_CONFIG_DIR_PATH = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle';

    const STORE_PATH = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/forms';

    const INVALID_FIELD_NAMES = [
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

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $backendConfig;

    /**
     * Configuration constructor.
     *
     */
    public function __construct()
    {
        $this->fileSystem = new FileSystem();
    }

    /**
     * @param array $config
     */
    public function setConfig($config = [])
    {
        $this->backendConfig = [
            'backend_base_field_type_groups' => $config['backend_base_field_type_groups'],
            'backend_base_field_type_config' => $config['backend_base_field_type_config'],
            // backend_field_type_config: not implemented yet.
            'backend_field_type_config'      => isset($config['backend_field_type_config']) ? $config['backend_field_type_config'] : []
        ];

        unset($config['backend_base_field_type_groups'], $config['backend_base_field_type_config'], $config['backend_field_type_config']);

        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getConfigArray()
    {
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getBackendConfigArray()
    {
        return $this->backendConfig;
    }

    /**
     * @param string $slot
     *
     * @return mixed
     */
    public function getConfig($slot)
    {
        return $this->config[$slot];
    }

    /**
     * @param string $flag
     *
     * @return bool
     */
    public function getConfigFlag($flag)
    {
        return $this->config['flags'][$flag];
    }

    /**
     * @param string $containerName
     *
     * @return mixed
     */
    public function getContainerFieldClass(string $containerName)
    {
        $containerTypes = $this->config['container_types'];

        return $containerTypes[$containerName]['class'];
    }

    /**
     * @return array
     */
    public function getAvailableContainer()
    {
        $containerTypes = $this->config['container_types'];

        $containerData = [];
        foreach ($containerTypes as $containerId => &$container) {

            if($container['enabled'] === false) {
                continue;
            }

            $container['id'] = $containerId;
            $containerData[$containerId] = $container;
        }

        return $containerData;
    }

    /**
     * @return array
     */
    public function getAvailableConstraints()
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

            $defaultProperties = $refClass->getDefaultProperties();
            $constraintConfig = [];
            foreach ($refClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $refProperty) {
                $propertyName = $refProperty->getName();

                if (in_array($propertyName, $invalidProperties)) {
                    continue;
                }

                if (isset($defaultProperties[$propertyName])) {
                    $defaultValue = $defaultProperties[$propertyName];
                    $type = gettype($defaultValue);
                } else {
                    $defaultValue = null;
                    $type = 'string';
                }

                if ($defaultValue === null || in_array(gettype($defaultValue), ['string', 'boolean', 'integer'])) {
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

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function getFieldTypeConfig($type)
    {
        return $this->config['types'][$type];
    }

    /**
     * @param string $slot
     *
     * @return mixed
     */
    public function getBackendConfig($slot)
    {
        return $this->backendConfig[$slot];
    }

    /**
     * @return mixed
     */
    public function getBackendConditionalLogicConfig()
    {
        return $this->config['conditional_logic'];
    }
}