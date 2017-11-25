# Conditional Logic
![](https://user-images.githubusercontent.com/700119/33080792-3835ab82-ced9-11e7-80c3-41da8940ceb3.png)

Use the conditional logic to hide/show fields, toggle classes, change validation and more, depending on given field values.

## Preparation
- Include the [conditional logic](91_Javascript.md#Conditional-logic-plugin) plugin
- Add the `fb-cl-hide-element` class with a `display:none;` property to your project style

## Conditions

| Name | Description | Options |
|------|-------------|---------|
| Element Value | Select fields which should have a conditional logic | Types: `selected`, `greater than`, `less than`, `equal`, `is not`, `checked` |

## Actions

| Name | Description | Require Depending Fields | Options |
|------|-------------|--------------------------|---------|
| Toggle Visibility | Change the visibility of one ore more fields. | Yes | State: `Show`, `Hide` |
| Add Validation | Add one or more validations to fields. | Yes | Validation-Types: Drop-Down of given Validations |
| Remove Validation | Remove one or more validations of fields. | Yes | All Types: remove all validations of selected fields |
| Change Value | Change the value of one ore more fields. | Yes | Value: *mixed* |
| Fire JS-Event | Fire custom javascript event of one ore more fields. | Yes | Event Name: *string* (only `a-z0-9.` chars) |
| Toggle Classes | Toggle a Class of one ore more fields | Yes | Class Name: *string* |
| Toggle Availability | Toggle a availability state | Yes | State: `Enabled`, `Disabled` |