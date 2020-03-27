# Object Channel

![image](https://user-images.githubusercontent.com/700119/77756495-91cb1200-702f-11ea-83b5-e05ba5716be5.png)

Use the mail channel to generate or enrich DataObjects.

## Configuration 

### Object Storage Path
Define, where all the objects should be stored.

### Resolver Strategy
Create a new object or append data to an existing one:

#### Create new Object
If you want to create a new object after each form submission, use this one. 
You need to choose a Dataclass afterwards.

You're able to map fields and field collections.

#### Use exiting One
If you want to append data to a existing object after each form submission, use this one.
You nee to define a object afterwards.

**Attention:** You're able to map field collections only!

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
| `Choice`         | `Text/Input`                                                   |
| `Dynamic Choice` | `Text/Input`                                                   |
| `Country` | unsupported                 |
| `Html Tag` | unsupported                |
| `Snippet` | unsupported                 |
| `reCAPTCHA v3` | unsupported            |
| `File` | unsupported                    |
| `Submit` | unsupported                  |

## Container Mapping
![image](https://user-images.githubusercontent.com/700119/77777525-fdbd7280-704f-11ea-9480-e89ac1c66edd.png)

This Channel also allows you to store `1:N` container data (Repeater, Fieldset) by storing them as a field collection in your data object.
You need to add the field collection relation to your form field. After that you're able to sub-map the field collection object.

## Events
You're able to implement a guard and enrichment event. Read more about it [here](./30_Events.md).