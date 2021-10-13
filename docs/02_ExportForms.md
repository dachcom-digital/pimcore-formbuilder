# Export Form

![export](https://user-images.githubusercontent.com/700119/43954901-7c59d7c8-9c9e-11e8-8930-2b6227604629.png)

It's possible to export submitted form mails as CSV.

## Options

| Name | Description
|------|------------|
| `All` | Export all mails |
| `... Output Workflow Name` | Filter by output workflows related to given form |

## Facts
- The header gets defined from the field `display_name` property and does not get translated
- If you rename the `display_name` afterwards, the new name gets applied in a separat row, no data will be lost
