<?php

namespace FormBuilderBundle\Transformer\Target;

class TargetAwareOutputTransformer
{
    /**
     * @var \Closure|array
     */
    protected $callable;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var TargetAwareData
     */
    protected $targetAwareData;

    /**
     * @param TargetAwareValue $awareValue
     * @param array            $arguments
     */
    public function __construct(TargetAwareValue $awareValue, array $arguments)
    {
        $this->callable = $awareValue->getCallback();
        $this->arguments = $arguments;
    }

    /**
     * @param mixed $target
     *
     * @return mixed|null
     */
    public function transform($target)
    {
        $arguments = array_merge([$target], $this->arguments);
        $this->targetAwareData = new TargetAwareData(...$arguments);

        if ($this->callable instanceof \Closure) {
            return call_user_func_array($this->callable, [$this->targetAwareData]);
        } elseif (is_array($this->callable)) {
            return call_user_func_array($this->callable, [$this->targetAwareData]);
        }

        return null;
    }
}
