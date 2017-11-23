<?php

namespace FormBuilderBundle\Configuration;

use Symfony\Component\Filesystem\Filesystem;

class Configuration
{
    const SYSTEM_CONFIG_DIR_PATH = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle';

    const SYSTEM_CONFIG_FILE_PATH = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/config.yml';

    const STORE_PATH = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/forms';

    const IMPORT_PATH = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/import';

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
            'backend_field_type_config'      => $config['backend_field_type_config']
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
     * @param $slot
     *
     * @return mixed
     */
    public function getConfig($slot)
    {
        return $this->config[$slot];
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    public function getFieldTypeConfig($type)
    {
        return $this->config['types'][$type];
    }

    /**
     * @param $slot
     *
     * @return mixed
     */
    public function getBackendConfig($slot)
    {
        return $this->backendConfig[$slot];
    }
}