<?php

namespace FormBuilderBundle\Exception\OutputWorkflow;

final class GuardChannelException extends GuardException
{
    /**
     * @param string          $message
     * @param \Exception|null $previousException
     */
    public function __construct(string $message, $previousException = null)
    {
        parent::__construct($message, 503, $previousException);
    }
}
