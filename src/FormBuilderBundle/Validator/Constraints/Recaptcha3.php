<?php

namespace FormBuilderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class Recaptcha3 extends Constraint
{
    const INVALID_FORMAT_ERROR = '4232b667-1360-4166-93ca-1769084f6304';

    public $message = 'We\'re sorry, but your computer or network may be sending automated queries. To protect our users, we can\'t process your request right now.';
}
