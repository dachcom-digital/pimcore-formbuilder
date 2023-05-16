# Form Data Injection
![image](https://github.com/dachcom-digital/pimcore-formbuilder/assets/700119/b6375824-bfeb-4705-8de1-36f174ebb7eb)

Sometimes it's required to set specific data for a given form field.
To do that, we usually fill up the `data` options field. 

In case this data should be fluid, you need to hook into some form events which requires developers to do that.

To make things easier, we've introduced the Data Injection Section.
There is also a preconfigured `expression` injector.

This feature is available for:
- Hidden Type
- Integer Type
- Text Type
- Textarea Type

***

## Expression Data Injector
![image](https://github.com/dachcom-digital/pimcore-formbuilder/assets/700119/53b9cde9-9057-4034-b1c3-895c5466c3ea)

Use this injector for the most common scenario: Link a request parameter to a given form.
Within the expression injecor, you're allowed to fetch data from symfonys `request` object.

Example: `request.query.get('myEventId')` or `request.attributes.get('siteId')`

***

## Custom Data Injector

```yaml
services:
    App\Form\DataInjector\MyDataInjector:
        tags:
            - { name: form_builder.data_injector, identifier: my_data_injector }
```

```php
<?php

namespace App\FormBuilder\Form\DataInjector;

class MyDataInjector implements DataInjectorInterface
{
    public function getName(): string
    {
        return 'My Data Injector';
    }

    public function getDescription(): ?string
    {
        return 'My Description';
    }

    public function parseData(array $config): mixed
    {
        if (!array_key_exists('my_additional_config_node', $config)) {
            return null;
        }

        $config = $config['my_additional_config_node'];
        
        // do your logic
        $newData = null;
        
        return $newData;
    }
}
```

```js
pimcore.registerNS('Formbuilder.extjs.form.dataInjection.my_data_injector');
Formbuilder.extjs.form.dataInjection.my_data_injector = Class.create({
    getForm: function (data) {
        return [{
            xtype: 'textfield',
            fieldLabel: 'Required Additional Config Node',
            name: 'my_additional_config_node',
            anchor: '100%',
            allowBlank: false,
            value: data !== null ? data.my_additional_config_node : null
        }];
    }
});

```