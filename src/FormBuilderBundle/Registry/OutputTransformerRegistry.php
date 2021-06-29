<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Transformer\Output\OutputTransformerInterface;

class OutputTransformerRegistry
{
    public const FALLBACK_TRANSFORMER_IDENTIFIER = 'fallback_transformer';

    protected array $transformer = [];

    public function register(string $identifier, string $channel, OutputTransformerInterface $service): void
    {
        if (!isset($this->transformer[$channel])) {
            $this->transformer[$channel] = [];
        }

        $this->transformer[$channel][$identifier] = $service;
    }

    public function hasForChannel(string $identifier, string $channel): bool
    {
        return
            (isset($this->transformer[$channel][$identifier]))
            || (isset($this->transformer['_all'][$identifier]));
    }

    public function getForChannel(string $identifier, string $channel): OutputTransformerInterface
    {
        if (!$this->hasForChannel($identifier, $channel)) {
            throw new \Exception('"' . $identifier . '" output transformer service does not exist.');
        }

        return $this->transformer[$channel][$identifier] ?? $this->transformer['_all'][$identifier];
    }

    public function getFallbackTransformer(): OutputTransformerInterface
    {
        return $this->getForChannel(self::FALLBACK_TRANSFORMER_IDENTIFIER, '_all');
    }

    public function getAll(): array
    {
        return $this->transformer;
    }
}
