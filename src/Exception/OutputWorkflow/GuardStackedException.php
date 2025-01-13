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

class GuardStackedException extends \Exception
{
    protected array $exceptions;

    public function __construct(array $guardExceptions, ?Throwable $previousException = null)
    {
        $this->exceptions = $guardExceptions;

        parent::__construct('Guard Stacked Exception', 0, $previousException);
    }

    public function getGuardExceptionMessages(): array
    {
        return array_map(function (GuardException $exception) {
            return $exception->getMessage();
        }, $this->exceptions);
    }
}
