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
