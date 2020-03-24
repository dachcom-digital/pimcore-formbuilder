<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

class GuardStackedException extends \Exception
{
    protected $exceptions;

    /**
     * @param GuardException[] $guardExceptions
     * @param \Exception|null  $previousException
     */
    public function __construct(array $guardExceptions, $previousException = null)
    {
        $this->exceptions = $guardExceptions;

        parent::__construct('Guard Stacked Exception', 0, $previousException);
    }

    /**
     * @return array
     */
    public function getGuardExceptionMessages()
    {
        return array_map(function (GuardException $exception) {
            return $exception->getMessage();
        }, $this->exceptions);
    }
}
