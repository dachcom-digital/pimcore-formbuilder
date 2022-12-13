<?php

namespace FormBuilderBundle\EventSubscriber\SignalStorage;

use FormBuilderBundle\Storage\StorageProviderInterface;

interface ProviderAwareStorageInterface
{
    public function setStorageProvider(StorageProviderInterface $storageProvider): void;

    public function getStorageProvider(): StorageProviderInterface;
}
