<?php

namespace FormBuilderBundle\Transformer\Target;

class TargetAwareOutputTransformer
{
    /**
     * @var \Closure|array
     */
    protected $callable;
    protected array $arguments;
    protected TargetAwareData $targetAwareData;

    public function __construct(TargetAwareValue $awareValue, array $arguments)
    {
        $this->callable = $awareValue->getCallback();
        $this->arguments = $arguments;
    }

    public function transform($target)
    {
        $arguments = array_merge([$target], $this->arguments);
        $this->targetAwareData = new TargetAwareData(...$arguments);

        if ($this->callable instanceof \Closure) {
            return call_user_func_array($this->callable, [$this->targetAwareData]);
        }

        if (is_array($this->callable)) {
            return call_user_func_array($this->callable, [$this->targetAwareData]);
        }

        return null;
    }
}
