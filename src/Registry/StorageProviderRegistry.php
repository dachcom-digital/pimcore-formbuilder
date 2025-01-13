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

use FormBuilderBundle\Storage\StorageProviderInterface;

class StorageProviderRegistry
{
    protected array $storageProvider = [];

    public function register(string $identifier, mixed $service): void
    {
        if (isset($this->storageProvider[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Storage Provider with identifier "%s" already exists', $identifier));
        }

        if (!in_array(StorageProviderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    StorageProviderInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->storageProvider[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->storageProvider[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): StorageProviderInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" storage provider does not exist.');
        }

        return $this->storageProvider[$identifier];
    }

    public function getAll(): array
    {
        return $this->storageProvider;
    }

    public function getAllIdentifier(): array
    {
        return array_keys($this->storageProvider);
    }
}
