# Field Transformer

Field transformer can be used to transform fields by configuration.  
For example this comes in handy if you're using the [API Output Channel](./09_ApiChannel.md).

![image](https://user-images.githubusercontent.com/700119/146228508-f155c865-c0ef-4703-a409-7f59aaa59839.png)

## Service Registration

 ```yaml
AppBundle\FormBuilder\FieldTransformer\PhoneNumberTransformer:
    autowire: true
    public: false
    tags:
        - { name: form_builder.output_workflow.field_transform, identifier: phoneNumberTransformer }

```

Within the service, you're able to modify your value.

```php
<?php

namespace AppBundle\FormBuilder\FieldTransformer;

use FormBuilderBundle\OutputWorkflow\FieldTransformerInterface;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberTransformer implements FieldTransformerInterface
{
    public function getName(): string
    {
        return 'Phone Number Transformer';
    }

    public function getDescription(): ?string
    {
        return 'Add your description here';
    }

    public function transform($value, array $context): mixed
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $phoneInstance = $phoneUtil->parse($value, 'DE');
        
        return $phoneUtil->format($phoneInstance, PhoneNumberFormat::E164);
    }
}
```
