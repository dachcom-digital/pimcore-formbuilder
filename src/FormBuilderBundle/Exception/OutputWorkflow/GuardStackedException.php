<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

class GuardStackedException extends \Exception
{
    protected array $exceptions;

    /**
     * @param GuardException[] $guardExceptions
     * @param \Exception|null  $previousException
     */
    public function __construct(array $guardExceptions, ?\Exception $previousException = null)
    {
        $this->exceptions = $guardExceptions;

        parent::__construct('Guard Stacked Exception', 0, $previousException);
    }

    public function getGuardExceptionMessages(): array
    {
        return array_map(static function (GuardException $exception) {
            return $exception->getMessage();
        }, $this->exceptions);
    }
}
