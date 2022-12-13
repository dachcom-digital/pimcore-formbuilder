## Funnels | Storage Provider
By default, FormBuilder uses a session storage provider.
It is not required to create a storage provider unless you have to.

However, creating a custom storage provider is very easy.
Your storage provider needs to implement the `StorageProviderInterface`:

### Configuration
```yaml
form_builder:
    funnel:
        enabled: true
        storage_provider: App\Storage\MyStorageProvider
```

### PHP Service
```php
<?php

namespace App\Storage;

class MyStorageProvider implements StorageProviderInterface
{
    public function store(Request $request, FormStorageData $formStorageData): string
    {
        $token = $this->generateToken();
        
        // @todo: store $formStorageData 
        
        return $token;
    }

    public function update(Request $request, string $token, FormStorageData $formStorageData): void
    {
        // @todo: update $formStorageData
    }

    public function flush(Request $request, string $token): void
    {
        // @todo: delete data
    }

    public function fetch(Request $request, string $token): ?FormStorageData
    {
        // @todo: fetch data  
    }
    
    protected function generateToken(): string
    {
        // @todo: generate unique token

        return sprintf('fbst-%s', Uid::v1()->toRfc4122());
    }
}
```
