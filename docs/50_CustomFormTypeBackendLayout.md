# Custom Form Type Backend Layout

### Available Backend Types
Render different configuration types for ExtJs.

| Name | Description |
|------|-------|
| label | -- |
| tagfield | -- |
| numberfield | -- |
| textfield | -- |
| select | -- |
| key_value_repeater | -- |
| options_repeater | -- |

### Custom Form Type Groups
Add your form type to one of these group fields (Context-Menu).

| Name | Description |
|------|-------|
| text_fields | -- |
| choice_fields | -- |
| datetime_fields | -- |
| other_fields | -- |
| field_groups | -- |
| hidden_fields | -- |
| buttons | -- |

### Tabs
Render your form type to a specific tab.

| Name | Description |
|------|-------|
| default | -- |

### Display Groups
Render your form type to a specific display group.

| Name | Description |
|------|-------|
| base | -- |
| attributes | -- |

***

## Options Transformer

Options Transformer will help you to generate valid options for backend and frontend.
For example, the (complex) [choices options](http://symfony.com/doc/current/reference/forms/types/choice.html#grouping-options) need to be transformed for the ExtJs context for backend configuration. 

### Choices Transformer
This transformer will transform the options array into a valid backend configuration array and back.

**Example**  
```yaml
options.choices:
    display_group_id: attributes
    type: key_value_repeater
    label: 'form_builder_type_field.choices'
    options_transformer: 'form_builder.options_transformer.choices'
    config: ~
```

## Default Value Transformer
If you want to define a default value, if the user leaves the field empty, you need the default value transformer.
In this example the field `placeholder` needs to be a boolean if left empty. Also it should stay empty, if it's get reopened in backend.

**Example**  
```yaml
options.placeholder:
    display_group_id: attributes
    type: textfield
    label: 'form_builder_type_field.placeholder'
    options_transformer: 'form_builder.options_transformer.default_value'
    config:
        default_value: false
```