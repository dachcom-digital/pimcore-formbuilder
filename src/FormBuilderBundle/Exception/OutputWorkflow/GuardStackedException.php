<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

use Throwable;

class GuardStackedException extends \Exception
{
    protected array $exceptions;

    public function __construct(array $guardExceptions, ?Throwable $previousException = null)
    {
        $this->exceptions = $guardExceptions;

        parent::__construct('Guard Stacked Exception', 0, $previousException);
    }

    public function getGuardExceptionMessages(): array
    {
        return array_map(function (GuardException $exception) {
            return $exception->getMessage();
        }, $this->exceptions);
    }
}
