# Input Transformer

Input Transformers will be applied, before a form gets pre-populated with given data.

Mostly this happens, if you're using a funnel output workflow.
There, the form data gets normalized and needs to be transformed back, before we can populate a form with data.

## Available Core Input Transformer

| Available Transformer | Field                                  | Service                                                           |
|-----------------------|----------------------------------------|-------------------------------------------------------------------|
| `date_transformer`    | `date`, `datetime`, `time`, `birthday` | `@FormBuilderBundle\Transformer\Output\DateDataObjectTransformer` |

> **Don't get confused!**   
> Some core output transformers also supports input transformation.
> Since we won't the namespace that because of BC reasons, we'll keep it that way (for now).  

## Override default Input Transformer

If you want to transform the text field value for example, you need to add your own transformer.
First, let FormBuilder know about your transformer.

> You're also able to set up your custom input transform for your dynamic fields via the `input_transformer` configuration node.

 ```yaml
form_builder:
    types:
        text:
            input_transformer: text_input_transformer
```

Now you need to register your new transformer:

```yaml
    App\OutputTransformer\TextInputTransformer:
        autowire: true
        tags:
            - { name: form_builder.transformer.input, type: text_input_transformer }
```

*** 

After that, you have to set up a PHP-Service:

```php
<?php

namespace App\InputTransformer;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Input\InputTransformerInterface;

class TextInputTransformer implements InputTransformerInterface
{
     public function getValueReverse(FieldDefinitionInterface $fieldDefinition, mixed $formValue): string
    {
        // manipulate or change the value
        return $rawValue;
    }
}
```

***

## Custom input transformer

After you've created a [custom form type](./../40_CustomFormType.md) (and/or you need it in a funnel output chanel),
you may want to control the input values too. Configure your PHP Service same as explained above.

```yaml
form_builder:
    types:
        your_custom_type:
            input_transformer: custom_input_transformer

services:
    App\OutputTransformer\MyCustomInputTransformer:
        autowire: true
        tags:
            - { name: form_builder.transformer.input, type: custom_input_transformer }
```
