<?php

namespace FormBuilderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class FriendlyCaptcha extends Constraint
{
    public string $message = 'We\'re sorry, but your computer or network may be sending automated queries. To protect our users, we can\'t process your request right now.';
}
