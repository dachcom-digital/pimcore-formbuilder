<?php

namespace FormBuilderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class EmailChecker extends Constraint
{
    public string $message = 'This value is not a valid email address.';
}
