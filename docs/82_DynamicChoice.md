# Dynamic Choice
The Dynamic Choice Type allows to generate Drop-Down / Checkbox / Radio Elements with dynamic content. For Example a Product List.

## Usage
In the FormBuilder Backend add a "Dynamic Choice Type" Field. There you'll find a "Service Name" Field with the defined Services. 
In the next Section you'll learn how to add such services.

## Add Service
```yaml
AppBundle\Services\FormBuilderBundle\ProductChoices:
    autowire: true
    public: false
    tags:
        - { name: form_builder.dynamic_choice_builder, label: 'Product Selector' }
```

## PHP Service
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