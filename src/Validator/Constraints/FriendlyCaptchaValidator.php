<?php

namespace FormBuilderBundle\Validator\Constraints;

use FormBuilderBundle\Tool\FriendlyCaptchaProcessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class FriendlyCaptchaValidator extends ConstraintValidator
{
    public function __construct(protected FriendlyCaptchaProcessor $friendlyCaptchaProcessor)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value !== null && !is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$constraint instanceof FriendlyCaptcha) {
            throw new UnexpectedTypeException($constraint, FriendlyCaptcha::class);
        }

        $value = null !== $value ? (string) $value : '';
        if (!$this->validateCaptcha($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }

    private function validateCaptcha(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return $this->friendlyCaptchaProcessor->verify($value)->isSuccess();
    }
}
