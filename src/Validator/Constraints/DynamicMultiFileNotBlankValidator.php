<?php

namespace FormBuilderBundle\Validator\Constraints;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DynamicMultiFileNotBlankValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DynamicMultiFileNotBlank) {
            throw new UnexpectedTypeException($constraint, DynamicMultiFileNotBlank::class);
        }

        if (!isset($value['adapter'])) {
            return;
        }

        if (!isset($value['adapter']['data'])) {
            return;
        }

        if (!is_array($value['adapter']['data'])) {
            $this->context->buildViolation($constraint->message)->addViolation();
        } elseif (count($value['adapter']['data']) === 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
