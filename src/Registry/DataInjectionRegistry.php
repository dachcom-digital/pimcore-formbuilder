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

use FormBuilderBundle\Form\DataInjector\DataInjectorInterface;

class DataInjectionRegistry
{
    protected array $services = [];

    public function register(string $identifier, $service): void
    {
        if (!in_array(DataInjectorInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), DataInjectorInterface::class, implode(', ', class_implements($service)))
            );
        }

        if (isset($this->services[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Data injector "%s" already has been registered.', $identifier));
        }

        $this->services[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): DataInjectorInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('Data injector "' . $identifier . '" does not exist.');
        }

        return $this->services[$identifier];
    }

    /**
     * @return array<int, DataInjectorInterface>
     */
    public function getAll(): array
    {
        return $this->services;
    }
}
