# Available Form Types

There are several form types available (updated continuously).

| Name | Group | Description |
|------|-------|-------------|
| Text | Text Fields | Default input text field. |
| Hidden | Hidden Fields | A hidden input field. |
| Text Area | Text Fields | Default textarea field. |
| Integer | Text Fields | HTML5 Number field. |
| Checkbox | Other Fields | Default (single) checkbox field. |
| Submit | Buttons | Submit Button. |
| Country | Choice Fields| Create a drop down with a default country selection. |
| Choice | Choice Fields | Create a drop down, multi drop down, multi-checkboxes, multi-radios, checkout [choice meta](90_FrontendTips.md#choice-meta-attributes) for enhanced choice customization |
| [Dynamic Choice](82_DynamicChoice.md) | Choice Fields | Create a drop down, multi drop down, multi-checkboxes, multi-radios. Populate Data from a custom Service.|
| Html Tag | Other Fields | Create a label, or headline element (tags can be defined via parameter) |
| Snippet | Other Fields | Place a custom (localized) snippet in your form. |
| [Dynamic Multi File](80_FileUpload.md) | Other Fields | Multi-File Upload field. |
| Date | Date and Time Fields | A field that allows the user to modify date information via a variety of different HTML elements. Now supports usage of text- or html5-datetype |
| Date Time | Date and Time Fields | This field type allows the user to modify data that represents a specific date and time (e.g. 1984-06-05 12:15:30). |
| Time | Date and Time Fields | This can be rendered as a text field, a series of text fields (e.g. hour, minute, second) or a series of select fields. The underlying data can be stored as a DateTime object, a string, a timestamp or an array. |
| Birthday | Date and Time Fields | Can be rendered as a single text box, three text boxes (month, day and year), or three select boxes. |
| [reCAPTCHA v3](03_SpamProtection.md) | Security Fields | Add an invisible Spam protection to your form.  |

# Available Container Form Types

There are several container form types available.
Read more about the [Container Type](84_ContainerType.md) here.

| Name | Group | Description |
|------|-------|-------------|
| Repeater | Container | Create repeatable field collections |
| Fieldset | Container | Create grouped field blocks |

## Constraints

| Name | Description |
|------|-------|
| NotBlank | Validates that a value is not blank - meaning not equal to a blank string, a blank array or null |
| Email | Validates that a value is a valid email address. The underlying value is cast to a string before being validated. |
| Length | Validates that a given string length is between some minimum and maximum value. |
| Url | Validates that a value is a valid URL string. |
| Regex | Validates that a value matches a regular expression. |
| IP-Address | Validates that a value is a valid IP address.  |
| Range | Validates that a given number is between some minimum and maximum number. |
| Card Scheme | This constraint ensures that a credit card number is valid for a given credit card company. |
| BIC| This constraint is used to ensure that a value has the proper format of a Business Identifier Code (BIC).  |
| Iban | This constraint is used to ensure that a bank account number has the proper format of an International Bank Account Number (IBAN). |
| Date | Validates that a value is a valid date, meaning either a DateTime object or a string (or an object that can be cast into a string) that follows a valid YYYY-MM-DD format. |
| DateTime | Validates that a value is a valid "datetime", meaning either a DateTime object or a string (or an object that can be cast into a string) that follows a specific format. |
| Time | Validates that a value is a valid time, meaning an object implementing DateTimeInterface or a string (or an object that can be cast into a string) that follows a valid HH:MM:SS format. |

## Enable / Disable Form Types
It's possible to enable or disable specific form types.

### Enable specific Elements
Only the `hidden` Element is available.

```yaml
form_builder:
    admin:
        active_elements:
            fields:
                - 'hidden'
```

### Disable specific Elements
All Fields except the `hidden` Element are available.

```yaml
form_builder:
    admin:
        inactive_elements:
            fields:
                - 'hidden'
```

> **Note**: If you're using both config elements at the same time, no field will show up (assuming that you're using the example from above)

## Extended Type Configuration

### Html Tag Type
To define your custom html tags, add this to your `app/config/config.yml':

```yaml
parameters:
    form_builder_html_tag_elements:
        - ['h1','h1']
        - ['h2','h2']
        - ['label','label']
        - ['p','p']
```
