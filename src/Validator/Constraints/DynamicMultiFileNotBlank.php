<?php

namespace FormBuilderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DynamicMultiFileNotBlank extends Constraint
{
    public string $message = 'This value should not be blank.';

    public function __construct(?array $options = null, ?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct($options ?? [], $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
