<?php

namespace FormBuilderBundle\Validator\Constraints;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DynamicMultiFileNotBlankValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DynamicMultiFileNotBlank) {
            throw new UnexpectedTypeException($constraint, DynamicMultiFileNotBlank::class);
        }

        if(!isset($value['data'])) {
            return;
        }

        if (!is_array($value['data'])) {
            $this->context->buildViolation($constraint->message)->addViolation();
        } elseif (count($value['data']) === 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
