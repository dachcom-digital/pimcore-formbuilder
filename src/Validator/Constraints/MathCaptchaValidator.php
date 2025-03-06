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

final class MathCaptchaValidator extends ConstraintValidator
{
    public function __construct(protected MathCaptchaProcessor $mathCaptchaProcessor)
    {
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

        if (!$this->validateCaptcha($challenge, $hash)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }

    private function validateCaptcha(?string $value, $hash): bool
    {
        if ($value === '' || $hash === null) {
            return false;
        }

        return $this->mathCaptchaProcessor->verify((int) $value, $hash);
    }
}
