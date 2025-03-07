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

use FormBuilderBundle\Tool\CloudflareTurnstileProcessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CloudflareTurnstileValidator extends ConstraintValidator
{
    public function __construct(protected CloudflareTurnstileProcessor $cloudflareTurnstileProcessor)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value !== null && !is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$constraint instanceof CloudflareTurnstile) {
            throw new UnexpectedTypeException($constraint, CloudflareTurnstile::class);
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

        return $this->cloudflareTurnstileProcessor->verify($value)->isSuccess();
    }
}
