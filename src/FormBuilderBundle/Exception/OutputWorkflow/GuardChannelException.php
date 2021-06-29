<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

final class GuardChannelException extends GuardException
{
    public function __construct(string $message, ?\Exception $previousException = null)
    {
        parent::__construct($message, 503, $previousException);
    }
}
