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

use FormBuilderBundle\OutputWorkflow\DynamicObjectResolver\DynamicObjectResolverInterface;

class DynamicObjectResolverRegistry
{
    protected array $services = [];

    public function register(string $identifier, string $label, mixed $service): void
    {
        if (!in_array(DynamicObjectResolverInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), DynamicObjectResolverInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = ['service' => $service, 'label' => $label];
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): DynamicObjectResolverInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dynamic object resolver service does not exist.');
        }

        return $this->services[$identifier]['service'];
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
