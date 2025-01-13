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

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class SimpleReturnStack implements ReturnStackInterface
{
    public function __construct(
        protected string $actionType,
        protected mixed $data
    ) {
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
