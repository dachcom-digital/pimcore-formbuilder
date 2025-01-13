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

use Symfony\Component\Validator\Constraint;

final class Recaptcha3 extends Constraint
{
    public const INVALID_FORMAT_ERROR = '4232b667-1360-4166-93ca-1769084f6304';

    public string $message = 'We\'re sorry, but your computer or network may be sending automated queries. To protect our users, we can\'t process your request right now.';
}
