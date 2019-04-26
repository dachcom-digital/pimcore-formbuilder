<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Transformer\Output\OutputTransformerInterface;

class OutputTransformerRegistry
{
    /**
     * @var array
     */
    protected $transformer = [];

    /**
     * @param string                     $identifier
     * @param string                     $channel
     * @param OutputTransformerInterface $service
     */
    public function register($identifier, string $channel, $service)
    {
        if (!in_array(OutputTransformerInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), OutputTransformerInterface::class,
                    implode(', ', class_implements($service)))
            );
        }

        if (!isset($this->transformer[$channel])) {
            $this->transformer[$channel] = [];
        }

        $this->transformer[$channel][$identifier] = $service;
    }

    /**
     * @param string $identifier
     * @param string $channel
     *
     * @return bool
     */
    public function hasForChannel($identifier, $channel)
    {
        return
            (isset($this->transformer[$channel]) && isset($this->transformer[$channel][$identifier]))
            || (isset($this->transformer['_all']) && isset($this->transformer['_all'][$identifier]));
    }

    /**
     * @param string $identifier
     * @param string $channel
     *
     * @return OutputTransformerInterface
     *
     * @throws \Exception
     */
    public function getForChannel($identifier, $channel)
    {
        if (!$this->hasForChannel($identifier, $channel)) {
            throw new \Exception('"' . $identifier . '" output transformer service does not exist.');
        }

        if (isset($this->transformer[$channel]) && isset($this->transformer[$channel][$identifier])) {
            return $this->transformer[$channel][$identifier];
        }

        return $this->transformer['_all'][$identifier];
    }

    /**
     * @return OutputTransformerInterface
     * @throws \Exception
     */
    public function getFallbackTransformer()
    {
        return $this->getForChannel('fallback_transformer', '_all');
    }

    /**
     * @return array|OutputTransformerInterface[]
     */
    public function getAll()
    {
        return $this->transformer;
    }
}
