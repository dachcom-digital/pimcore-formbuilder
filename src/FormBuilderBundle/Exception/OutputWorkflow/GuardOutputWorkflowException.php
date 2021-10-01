<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

use Throwable;

final class GuardOutputWorkflowException extends GuardException
{
    public function __construct(string $message, ?Throwable $previousException = null)
    {
        parent::__construct($message, 503, $previousException);
    }
}
