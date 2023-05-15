# Runtime Data
Some attributes are available at runtime (The [additional] configuration data in area bricks, for example).
They will be stored in a hidden field but will be picked up very early, so it's possible to get those configuration data in all `PRE_SET_DATA` Events.

FormBuilder itself requires the runtime data provider if you're using runtime data object resolver in the object output channel. 
Read more about it [here](./OutputWorkflow/11_ObjectChannel.md#dynamic-object-resolver).

## Add additional runtime data

```yaml
services:
    App\FormBuilder\RuntimeData\Provider\MyRuntimeData:
        autowire: true
        tags:
            - { name: form_builder.runtime_data_provider}
```

```php
<?php

namespace App\FormBuilder\RuntimeData\Provider;

use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;

class MyRuntimeData implements RuntimeDataProviderInterface
{
    public function getRuntimeDataId(): string
    {
        return 'my_runtime_options_identifier';
    }

    public function hasRuntimeData(FormDefinitionInterface $formDefinition): bool
    {
        // add your logic here.
        $hasData = false;

        return $hasData;
    }

    public function getRuntimeData(FormDefinitionInterface $formDefinition): mixed
    {
        return 'my_runtime_option_value';
    }
}
```

### Availability
From now on, your `my_runtime_options_identifier` option is available in all the form builder `formRuntimeData` configuration node, 
for example in the `ChannelSubjectGuardEvent` event.