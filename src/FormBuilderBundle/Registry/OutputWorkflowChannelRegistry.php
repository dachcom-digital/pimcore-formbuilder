<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class OutputWorkflowChannelRegistry
{
    /**
     * @var array
     */
    protected $channels = [];

    /**
     * @param string           $identifier
     * @param ChannelInterface $service
     */
    public function register($identifier, $service)
    {
        if (isset($this->channels[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Output Channel with identifier "%s" already exists', $identifier));
        }

        if (!in_array(ChannelInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    ChannelInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->channels[$identifier] = $service;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->channels[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return ChannelInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" output workflow channel does not exist.');
        }

        return $this->channels[$identifier];
    }

    /**
     * @return array|ChannelInterface[]
     */
    public function getAll()
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function getAllIdentifier()
    {
        return array_keys($this->channels);
    }
}
