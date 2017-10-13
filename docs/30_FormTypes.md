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
| Choice | Choice Fields | Create a dropdown, multi-dropdown, multi-checkboxes, multi-radios |
| Html Tag | Other Fields | Create a label, or headline element (tags can be defined via parameter) |
| Snippet | Other Fields | Place a custom snippet in your form. |
| [Dynamic Multi File](80_FileUpload.md) | Other Fields | Multi-File Upload field. |

## Constraints

| Name | Description |
|------|-------|
| NotBlank | Validates that a value is not blank - meaning not equal to a blank string, a blank array or null |
| Email | Validates that a value is a valid email address. The underlying value is cast to a string before being validated. |

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
All Fields except the `hidden` Element is available.

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