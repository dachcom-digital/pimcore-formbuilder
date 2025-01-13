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

namespace FormBuilderBundle\Validator\EmailChecker;

final class EmailCheckerProcessor
{
    public function __construct(protected iterable $emailChecker)
    {
    }

    public function isValid(string $email, array $context = []): bool
    {
        /** @var EmailCheckerInterface $emailChecker */
        foreach ($this->emailChecker as $emailChecker) {
            if (!$emailChecker->isValid($email, $context)) {
                return false;
            }
        }

        return true;
    }
}
