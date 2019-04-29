# Output Transformer

Defining a special transformer is quite simple:

### Override default output transformer
 
First, let FormBuilder know about your transformer. 

> You're also able to set up your custom output transform for your custom fields.

 ```yaml
form_builder:
    types:
        text:
            output_transformer: text_input_transformer
```

> By default, every field uses the `fallback_transformer` which come within the FormBuilder core.

Now you need to register your new and shiny transformer:

```yaml
    AppBundle\OutputTransformer\TextInputTransformer:
        autowire: true
        tags:
            - { name: form_builder.transformer.output, type: text_input_transformer, channel: _all }
```

***
#### Wait! Channels?
Currently, only `_all` or `mail` is a valid channel. 
We're planning to add some more output channels (like `object`) in the near future.
*** 

Ok, back on track. After that, you have to set up a PHP class:

```php
<?php

namespace AppBundle\OutputTransformer;

use FormBuilderBundle\Storage\FormFieldSimpleInterface;
use FormBuilderBundle\Transformer\Output\OutputTransformerInterface;
use Pimcore\Translation\Translator;
use Symfony\Component\Form\FormInterface;

class TextInputTransformer implements OutputTransformerInterface
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        // manipulate or change the value
        return $rawValue;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        // manipulate or change the label
        return $rawValue;
    }
}
```