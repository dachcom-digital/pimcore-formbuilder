# Dynamic Choice
The Dynamic Choice Type allows to generate Drop-Down / Checkbox / Radio Elements with dynamic content. For Example a Product List.

## Usage
In the FormBuilder Backend add a "Dynamic Choice Type" Field. There you'll find a "Service Name" Field with the defined Services. 
In the next Section you'll learn how to add such services.

## Error Handling
**Important**: It's not possible to add custom validation messages within the DynamicChoice Service. 
That's because you're dealing with a already rendered form type (dynamic choice). 
To add some validations you could use the default constraints in the form builder itself or
by extending the default options via [form extensions](http://symfony.com/doc/current/form/create_form_type_extension.html).

## Add Service
```yaml
AppBundle\Services\FormBuilderBundle\ProductChoices:
    autowire: true
    public: false
    tags:
        - { name: form_builder.dynamic_choice_builder, label: 'Product Selector' }
```

## PHP Service (Simple)
```php
<?php

namespace AppBundle\Services\FormBuilderBundle;

use FormBuilderBundle\Form\ChoiceBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Model\Product;

class ProductChoices implements ChoiceBuilderInterface
{
    protected $builder;

    public function setFormBuilder(FormBuilderInterface $builder)
    {
        $this->builder = $builder;

        // transform data back to string (to display the product name in the email for example)
        $builder->addModelTransformer(new CallbackTransformer(
            function ($entries) {
                return $entries;
            },
            function ($entries) {
                if (empty($entries)) {
                    return $entries;
                }

                if (is_array($entries)) {
                    $data = [];
                    foreach ($entries as $id) {
                        $product = Product::getById($id);
                        $data[] = $product->getName();
                    }
                    return implode(', ', $data);
                } else {
                    $product = Product::getById($entries);
                    return $product->getName();
                }
            }
        ));
    }

    public function getList()
    {
        $product1 = Product::getById(89);
        $product2 = Product::getById(47);

        return [
            $product1->getName() => $product1->getId(),
            $product2->getName() => $product2->getId()
        ];
    }
}
```

## PHP Service (Advanced)
You could implement the `AdvancedChoiceBuilderInterface` to get more control about your choice element:

```php
<?php

namespace AppBundle\Services\FormBuilderBundle;

use FormBuilderBundle\Form\AdvancedChoiceBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Model\Product;

class ProductChoices implements AdvancedChoiceBuilderInterface
{
    protected $builder;

    public function setFormBuilder(FormBuilderInterface $builder)
    {
        // same as in simple service
    }

    public function getList()
    {
        // same as in simple service
    }

    public function getChoiceValue($element = null)
    {
        // @see: https://symfony.com/doc/current/reference/forms/types/choice.html#choice-value
    }

    public function getChoiceLabel($element, $key, $index)
    {
        // @see: https://symfony.com/doc/current/reference/forms/types/choice.html#choice-label
    }

    public function getChoiceAttributes($element, $key, $index)
    {
        // @see: https://symfony.com/doc/current/reference/forms/types/choice.html#choice-attr
    }

    public function getGroupBy($element, $key, $index)
    {
        // @see: https://symfony.com/doc/current/reference/forms/types/choice.html#group-by
    }

    public function getPreferredChoices($element, $key, $index)
    {
        // @see: https://symfony.com/doc/current/reference/forms/types/choice.html#preferred-choices
    }
}
```