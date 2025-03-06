<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Validator\Constraints;

use FormBuilderBundle\Tool\MathCaptchaProcessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MathCaptchaValidator extends ConstraintValidator
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected MathCaptchaProcessor $mathCaptchaProcessor
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value !== null && !is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if (!$constraint instanceof MathCaptcha) {
            throw new UnexpectedTypeException($constraint, MathCaptcha::class);
        }

        $challenge = $value['challenge'] ?? null;
        $hash = $value['hash'] ?? null;
        $stamp = $value['stamp'] ?? null;

        $validationState = $this->validateCaptcha($challenge, $hash, $stamp);

        if ($validationState === MathCaptchaProcessor::VALIDATION_STATE_VALID) {
            return;
        }

        $validationMessage = $validationState === MathCaptchaProcessor::VALIDATION_STATE_EXPIRED
            ? $constraint->expiredMessage
            : $constraint->invalidValueMessage;

        $this->context
            ->buildViolation($this->translator->trans($validationMessage))
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->addViolation();
    }

    private function validateCaptcha(?string $value, ?string $hash, ?string $stamp): string
    {
        if ($value === '' || $value === null || $hash === null || $stamp === null) {
            return MathCaptchaProcessor::VALIDATION_STATE_INVALID_VALUE;
        }

        return $this->mathCaptchaProcessor->verify((int) $value, $hash, $stamp);
    }
}
