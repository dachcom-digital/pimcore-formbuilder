<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

use Throwable;

final class GuardChannelException extends GuardException
{
    public function __construct(string $message, ?Throwable $previousException = null)
    {
        parent::__construct($message, 503, $previousException);
    }
}
