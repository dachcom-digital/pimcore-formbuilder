<?php

namespace FormBuilderBundle\Transformer\Target;

class TargetAwareValue
{
    protected \Closure|array $callback;

    public function __construct(\Closure|array $callback)
    {
        $this->callback = $callback;
    }

    public function getCallback(): \Closure|array
    {
        return $this->callback;
    }
}
