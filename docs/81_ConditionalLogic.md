# Conditional Logic
![](https://user-images.githubusercontent.com/700119/43034880-05fca358-8ce5-11e8-8fc4-2954fc7b942d.png)

Use the conditional logic to hide/show fields, toggle classes, change validation and more, depending on given field values.

## Preparation
- Include the [conditional logic](91_Javascript.md) extension
- Add the `fb-cl-hide-element` class with a `display:none;` property to your project style

## Conditions

| Name | Description | Options |
|------|-------------|---------|
| Element Value | Select fields which should have a conditional logic | Types: `selected`, `greater than`, `less than`, `equal`, `is not`, `checked` |
| Output Workflow | Select output workflow to apply conditional logic | All available form output workflows |

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
| Switch Output Workflow | Switch entire Output Workflow | No | None |
| Success Message | Change Success Message (**Warning!** Use this action with caution. If there is no "Output Workflow" condition, this action will apply to all given output workflows of given form) | No | Type: `Text`, `Snippet`, `Redirect to Document`, `Redirect to external Page` |