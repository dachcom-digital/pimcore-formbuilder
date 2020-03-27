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
This table shows all possible data mapping fields:

| FormBuilder Type | Allowed Pimcore Type |
|------------------|----------------------|
| `Text`           | `Text/Input`         |
| `Hidden` | unsupported                  |
| `Text Area` | unsupported               |
| `Integer` | unsupported                 |
| `Checkbox` | unsupported                |
| `Submit` | unsupported                  |
| `Country` | unsupported                 |
| `Choice` | unsupported                  |
| `Dynamic Choice` | unsupported          |
| `Date` | unsupported                    |
| `Date Time` | unsupported               |
| `Time` | unsupported                    |
| `Birthday` | unsupported                |  
| `Html Tag` | unsupported                |
| `Snippet` | unsupported                 |
| `reCAPTCHA v3` | unsupported            |
| `File` | unsupported                    |

## Events
You're able to implement a guard and enrichment event. Read more about it [here](./30_Events.md).