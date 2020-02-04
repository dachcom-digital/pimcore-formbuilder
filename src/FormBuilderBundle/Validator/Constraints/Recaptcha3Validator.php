<?php

namespace FormBuilderBundle\Validator\Constraints;

use FormBuilderBundle\Tool\ReCaptchaProcessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class Recaptcha3Validator extends ConstraintValidator
{
    /**
     * @var ReCaptchaProcessorInterface
     */
    protected $reCaptchaProcessor;

    /**
     * @param ReCaptchaProcessorInterface $reCaptchaProcessor
     */
    public function __construct(ReCaptchaProcessorInterface $reCaptchaProcessor)
    {
        $this->reCaptchaProcessor = $reCaptchaProcessor;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value !== null && !is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$constraint instanceof Recaptcha3) {
            throw new UnexpectedTypeException($constraint, Recaptcha3::class);
        }

        $value = null !== $value ? (string) $value : '';
        if (!$this->validateCaptcha($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Recaptcha3::INVALID_FORMAT_ERROR)
                ->addViolation();
        }
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function validateCaptcha(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $response = $this->reCaptchaProcessor->verify($value);

        return $response->isSuccess();
    }
}
