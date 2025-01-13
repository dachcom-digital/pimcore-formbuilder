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

namespace FormBuilderBundle\Exception\OutputWorkflow;

use Throwable;

final class GuardOutputWorkflowException extends GuardException
{
    public function __construct(string $message, ?Throwable $previousException = null)
    {
        parent::__construct($message, 503, $previousException);
    }
}
