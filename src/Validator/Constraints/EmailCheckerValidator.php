<?php

namespace FormBuilderBundle\Validator\Constraints;

use FormBuilderBundle\Validator\EmailChecker\EmailCheckerProcessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class EmailCheckerValidator extends ConstraintValidator
{
    public function __construct(protected EmailCheckerProcessor $emailCheckerProcessor)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EmailChecker) {
            throw new UnexpectedTypeException($constraint, EmailChecker::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!\is_scalar($value) && !$value instanceof \Stringable) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string) $value;
        if ($value === '') {
            return;
        }

        if (!$this->validateEmailAddress($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }

    private function validateEmailAddress(string $value): bool
    {
        return $this->emailCheckerProcessor->isValid($value);
    }
}
