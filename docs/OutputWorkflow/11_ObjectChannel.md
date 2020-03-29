# Object Channel

![image](https://user-images.githubusercontent.com/700119/77756495-91cb1200-702f-11ea-83b5-e05ba5716be5.png)

Use the object channel to generate or enrich DataObjects.

## Configuration 
There are two different resolver strategies you can rely on.

### I. Create new Object
If you want to create a new object after each form submission, use this one. 
You need to choose a data class afterwards.

You're able to map fields and field collections.

#### Options

| Name                  | Description                                                    |
|-----------------------|----------------------------------------------------------------|
| `Object Storage Path`  | Define where all the objects should be stored.                |
| `Object Class`         | Choose, which data object type should be created.             |


### II. Use existing Object
If you want to append data to a existing object after each form submission, use this one.
You need to define an object reference afterwards.

**Attention:** You're able to map field collections only!

#### Options

| Name                      | Description                                                    |
|---------------------------|----------------------------------------------------------------|
| `Referencing Object`      | Define where all the objects should be stored.                 |
| `Dynamic Object Resolver` | If any resolver has been registered, you could choose one. Read more about the dynamic object resolver [below](./11_ObjectChannel.md#dynamic-object-resolver). |

***

## Available Mapping Data

![image](https://user-images.githubusercontent.com/700119/77777088-4f193200-704f-11ea-8d1b-168955d568f7.png)

This table shows all possible data mapping fields:

| FormBuilder Type | Allowed Pimcore Type                                           |
|------------------|----------------------------------------------------------------|
| `Text`           | `Text/Input`, `Text/Textarea`, `Text/Wysiwyg`                  |
| `Hidden`         | `Text/Input`, `Text/Textarea`, `Text/Wysiwyg`                  |
| `Text Area`      | `Text/Textarea`, `Text/Wysiwyg`                                |
| `Integer`        | `Text/Input`, `Text/Textarea`, `Text/Wysiwyg`, `Number/Number` |
| `Checkbox`       | `Other/Checkbox`                                               |
| `Date`           | `Date/Date`                                                    |
| `Date Time`      | `Date/DateTime`                                                |
| `Time`           | `Date/Time`                                                    |
| `Birthday`       | `Date/Date`                                                    | 
| `Choice`         | `Text/Input`,`Select/Select`, `Select/MultiSelect`             |
| `Dynamic Choice` | `Text/Input`,`Select/Select`, `Select/MultiSelect`             |
| `Country`        | `Text/Input`,`Select/Country`, `Select/Country (MultiSelect)`  |
| `Html Tag` | unsupported                |
| `Snippet` | unsupported                 |
| `reCAPTCHA v3` | unsupported            |
| `File` | unsupported                    |
| `Submit` | unsupported                  |

### Dynamic Choices Mapping
If you're using the dynamic choice type mapping, make sure your mapped pimcore field is connected to the same options provider source! 

## Container Mapping
![image](https://user-images.githubusercontent.com/700119/77777525-fdbd7280-704f-11ea-9480-e89ac1c66edd.png)

This Channel also allows you to store `1:N` container data (Repeater, Fieldset) by storing them as a field collection in your data object.
You need to add the field collection relation to your form field. After that you're able to sub-map the field collection object.

## Events
You're able to implement a guard and enrichment event. Read more about it [here](./30_Events.md).

## Dynamic Object Resolver

> Note: The Dynamic Object Resolver is only available if you're using the "Use existing Object" strategy!

Example: You want to show some events on your website. Every event has its own detail page on your website. To allow user to apply to your event, you may want to add a form at the bottom of those event pages.

After you've created an object output workflow you'll shortly run into a simple question: Does FormBuilder know, which existing event should be used to append the form data? 
The answer is simple: No. 

To achieve this, you need to do some configuration work. Luckily, FormBuilder ships some pre-configured services, so you only need to configure them properly.

### I. Add Runtime Data   
First, you need to determinate an object identifier. Mostly it's an ID in your query string.

```yaml
form_builder.form.runtime_data.event_id_fetcher:
    class: FormBuilderBundle\Form\RuntimeData\Provider\RequestDataProvider
    autowire: true
    arguments:
        $expr: 'request.query.get("eventId", null)'
        $runtimeId: 'event_id'
    tags:
        - { name: form_builder.runtime_data_provider}
```

### II. Add dynamic Object Resolver
Second, create a dynamic object resolver and append the found ID from the runtime data pool.

```yaml
form_builder.output_workflow.object.dynamic_object_resolver.event:
    class: FormBuilderBundle\OutputWorkflow\DynamicObjectResolver\ObjectByRuntimeDataResolver
    autowire: true
    arguments:
        $runtimeDataId: 'event_id'
        $objectIdentifier: 'name'
        $isLocalizedValue: false
    tags:
        - { name: form_builder.output_workflow.object.dynamic_resolver, identifier: 'event', label: 'My Event Object Resolver'}
```