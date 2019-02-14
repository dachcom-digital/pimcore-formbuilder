<?php

namespace FormBuilderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DynamicMultiFileNotBlank extends Constraint
{
    public $message = 'This value should not be blank.';
}
