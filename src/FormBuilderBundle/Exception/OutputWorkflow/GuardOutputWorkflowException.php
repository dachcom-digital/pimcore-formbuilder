<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

final class GuardOutputWorkflowException extends GuardException
{
    public function __construct(string $message, ?\Exception $previousException = null)
    {
        parent::__construct($message, 503, $previousException);
    }
}
