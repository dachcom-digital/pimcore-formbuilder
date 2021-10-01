# Output Transformer
Output Transformers allow you to modify every form field value differently for each given channel
before submitting them to a given output workflow channel.

## Available Core Transformer
If no transformer has been found, the `fallback_transformer` will be used.

| Available Transformer   | Field                                   | Channel      | Service                                                                 |
|-------------------------|-----------------------------------------|--------------|-------------------------------------------------------------------------|
| `date_transformer`      | `date`, `datetime`, `time`, `birthday`  | `object`     | `@FormBuilderBundle\Transformer\Output\DateDataObjectTransformer`       |
| `choice_transformer`    | `choice`, `dynamic_choice`              | `object`     | `@FormBuilderBundle\Transformer\Output\ChoiceDataObjectTransformer`     | 
| `country_transformer`   | `country`                               | `object`     | `@FormBuilderBundle\Transformer\Output\CountryDataObjectTransformer`    | 
| `fallback_transformer`  | All others                              | `_all`       | `@FormBuilderBundle\Transformer\Output\FallbackTransformer`             |

## Override default Output Transformer
If you want to transform the text field value for example, you need to add your own transformer.
First, let FormBuilder know about your transformer. 

> You're also able to set up your custom output transform for your dynamic fields via `output_transformer` in the optional option configuration node.

 ```yaml
form_builder:
    types:
        text:
            output_transformer: text_input_transformer
```

Now you need to register your new and shiny transformer. Since there is no default text field transformer,
you could apply this transformer to all channels.

```yaml
    AppBundle\OutputTransformer\TextInputTransformer:
        autowire: true
        tags:
            - { name: form_builder.transformer.output, type: text_input_transformer, channel: _all }
```

If you want to apply this transformer to the [object output channel](./15_OutputTransformer.md) only, change the channel:

```yaml
    AppBundle\OutputTransformer\TextInputTransformer:
        autowire: true
        tags:
            - { name: form_builder.transformer.output, type: text_input_transformer, channel: object }
```

*** 

After that, you have to set up a PHP-Service:

```php
<?php

namespace AppBundle\OutputTransformer;

use Pimcore\Translation\Translator;
use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Output\OutputTransformerInterface;

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
    public function getValue(FieldDefinitionInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        // manipulate or change the value
        return $rawValue;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(FieldDefinitionInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        // manipulate or change the label
        return $rawValue;
    }
}
```

***

## Custom output transformer

After you've created a [custom output channel](./12_CustomChannel.md), you may want to control the output values too.
It is possible to use one transformer for all available fields, like we'll show you below.
If you want to add a fallback to all the other fields in your channel, you need to add an additional tag with type `fallback_transformer`.

> **Note:** If you don't add any transformer, the `fallback_transformer` will be used again.
>
```yaml
AppBundle\OutputTransformer\MyChannelOutputTransformer:
    autowire: true
    tags:
        - { name: form_builder.transformer.output, type: date_transformer, channel: myChannel }
        - { name: form_builder.transformer.output, type: choice_transformer, channel: myChannel }
        - { name: form_builder.transformer.output, type: choice_transformer, channel: myChannel }
            # To use this transformer also for all the other fields, use the fallback_transformer.
            # In fact, if you're removing the tags above, this fallback will be used on every field
        - { name: form_builder.transformer.output, type: fallback_transformer, channel: myChannel }
```

## Target Aware Transformer
In some rare cases you may need the target field, before you're able to map the data.
In our object output channel for example, it's allowed to map choice values to dropdowns but also to input fields.

```php
<?php

namespace AppBundle\OutputTransformer;

use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Transformer\Target\TargetAwareData;
use FormBuilderBundle\Transformer\Target\TargetAwareValue;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Output\OutputTransformerInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;

class MyTargetAwareOutputTransformer implements OutputTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        return new TargetAwareValue([$this, 'getTargetAwareValue']);
    }

    /**
     * @param TargetAwareData $targetAwareData
     *
     * @return mixed|null
     */
    public function getTargetAwareValue(TargetAwareData $targetAwareData)
    {
        $rawValue = $targetAwareData->getRawValue();
        $target = $targetAwareData->getTarget();

        if ($target instanceof Select) {
            return is_array($rawValue) ? $rawValue[0] : $rawValue;
        } elseif ($target instanceof Multiselect) {
            return !is_array($rawValue) ? [$rawValue] : $rawValue;
        } elseif ($target instanceof Input) {
            return is_array($rawValue) ? join(', ', $rawValue) : $rawValue;
        }

        return $rawValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(FieldDefinitionInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        return null;
    }
}
```