<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Transformer\Target;

class TargetAwareOutputTransformer
{
    protected \Closure|array $callable;
    protected array $arguments;
    protected TargetAwareData $targetAwareData;

    public function __construct(TargetAwareValue $awareValue, array $arguments)
    {
        $this->callable = $awareValue->getCallback();
        $this->arguments = $arguments;
    }

    public function transform(mixed $target): mixed
    {
        $arguments = array_merge([$target], $this->arguments);
        $this->targetAwareData = new TargetAwareData(...$arguments);

        if ($this->callable instanceof \Closure) {
            return call_user_func_array($this->callable, [$this->targetAwareData]);
        }

        return call_user_func_array($this->callable, [$this->targetAwareData]);
    }
}
