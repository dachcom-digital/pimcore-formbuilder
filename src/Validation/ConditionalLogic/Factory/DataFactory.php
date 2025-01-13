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

namespace FormBuilderBundle\Validation\ConditionalLogic\Factory;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;

class DataFactory
{
    public function __construct(protected iterable $dataHandler)
    {
    }

    public function generate(string $serviceId): ?DataInterface
    {
        foreach ($this->dataHandler as $dataHandler) {
            if ($dataHandler instanceof $serviceId) {
                return $dataHandler;
            }
        }

        return null;
    }
}
