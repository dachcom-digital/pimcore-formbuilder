## Funnels | Custom Storage Class
In some rare scenarios, you may want to extend the signal storage.

### Configuration
```yaml
form_builder:
    funnel:
        enabled: true
        signal_storage_class: App\Storage\MySignalStorageClass
```

### PHP Service
```php
<?php

namespace App\Storage;

class MySignalStorageClass implements SignalStorageInterface, ProviderAwareStorageInterface
{
    protected FunnelData $funnelData;
    protected StorageProviderInterface $storageProvider;
    protected array $context;

    public function __construct(array $context = [])
    {
        $this->context = $context;

        // @todo: validate context
    }

    public function setStorageProvider(StorageProviderInterface $storageProvider): void
    {
        $this->storageProvider = $storageProvider;
    }

    public function getStorageProvider(): StorageProviderInterface
    {
        return $this->storageProvider;
    }

    public function storeSignal(OutputWorkflowSignalEvent $signal): void
    {
        // @todo: store signal
    }

    public function getSignals(): array
    {
        // @todo: load signals
    }
}
```