<?php

namespace FormBuilderBundle\Validator\EmailChecker;

interface EmailCheckerInterface
{
    public function isValid(string $email, array $context): bool;
}
