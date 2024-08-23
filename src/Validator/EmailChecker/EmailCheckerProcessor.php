<?php

namespace FormBuilderBundle\Validator\EmailChecker;

final class EmailCheckerProcessor
{
    public function __construct(protected iterable $emailChecker)
    {

    }

    public function isValid(string $email, array $context = []): bool
    {
        /** @var EmailCheckerInterface $emailChecker */
        foreach ($this->emailChecker as $emailChecker) {
            if (!$emailChecker->isValid($email, $context)) {
                return false;
            }
        }

        return true;
    }
}
