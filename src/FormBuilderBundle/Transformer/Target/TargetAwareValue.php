<?php

namespace FormBuilderBundle\Transformer\Target;

class TargetAwareValue
{
    /**
     * @var array|\Closure
     */
    protected $callback;

    /**
     * @param \Closure|array $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return \Closure|array
     */
    public function getCallback()
    {
        return $this->callback;
    }
}
