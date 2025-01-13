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

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

class SwitchOutputWorkflowData implements DataInterface
{
    public const IDENTIFIER = 'workflowName';

    private array $data = [];

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function hasData(): bool
    {
        return !empty($this->data);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function hasOutputWorkflowName(): bool
    {
        return !empty($this->data[self::IDENTIFIER]);
    }

    public function getOutputWorkflowName(): ?string
    {
        if (!$this->hasOutputWorkflowName()) {
            return null;
        }

        return $this->data[self::IDENTIFIER];
    }
}
