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

namespace FormBuilderBundle\OutputWorkflow\Channel;

class ChannelContext
{
    protected array $contextData = [];

    public function getAllContextData(): array
    {
        return $this->contextData;
    }

    public function hasContextData(string $key): bool
    {
        return array_key_exists($key, $this->contextData);
    }

    public function getContextData(string $key): mixed
    {
        return $this->contextData[$key] ?? [];
    }

    public function addContextData(string $key, mixed $data): void
    {
        $this->contextData[$key] = $data;
    }
}
