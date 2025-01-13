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

class FieldReturnStack implements ReturnStackInterface
{
    /**
     * @throws \Exception
     */
    public function __construct(
        protected string $actionType,
        protected mixed $data
    ) {
        if (!$this->isAssoc($this->data)) {
            throw new \Exception('FieldReturnStack: Wrong data structure: data keys must contain form field names!');
        }
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function updateData(mixed $data): void
    {
        $this->data = $data;
    }

    private function isAssoc(mixed $arr): bool
    {
        if (!is_array($arr)) {
            return false;
        }

        if ($arr === []) {
            return true;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
