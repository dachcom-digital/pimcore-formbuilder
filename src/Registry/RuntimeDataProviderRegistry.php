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

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;

class RuntimeDataProviderRegistry
{
    protected array $services = [];

    public function register(mixed $service): void
    {
        if (!in_array(RuntimeDataProviderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), RuntimeDataProviderInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[] = $service;
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
