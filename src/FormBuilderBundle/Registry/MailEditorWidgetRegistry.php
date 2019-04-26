<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\MailEditor\Widget\MailEditorWidgetInterface;

class MailEditorWidgetRegistry
{
    /**
     * @var array
     */
    protected $provider = [];

    /**
     * @param string                    $identifier
     * @param MailEditorWidgetInterface $service
     */
    public function register($identifier, $service)
    {
        if (!in_array(MailEditorWidgetInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    MailEditorWidgetInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->provider[$identifier] = $service;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->provider[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return MailEditorWidgetInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" widget service does not exist.');
        }

        return $this->provider[$identifier];
    }

    /**
     * @return array|MailEditorWidgetInterface[]
     */
    public function getAll()
    {
        return $this->provider;
    }

    /**
     * @return array
     */
    public function getAllIdentifier()
    {
        return array_keys($this->provider);
    }
}
