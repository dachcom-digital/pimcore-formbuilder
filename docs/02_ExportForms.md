# Export Form

![export](https://user-images.githubusercontent.com/700119/43954901-7c59d7c8-9c9e-11e8-8930-2b6227604629.png)

It's possible to export submitted form mails as CSV.

## Options

| Name | Description
|------|------------|
| `All` | Export all mails |
| ~`Only Admin Mail`~ | **Deprecated!** Only export all main mails |
| ~`Only User Mail`~ | **Deprecated!** Only export all user mails (defined in copy field) |

## Facts
- The header gets defined from the field `display_name` property and does not get translated
- If you rename the `display_name` afterwards, the new name gets applied in a separat row, no data will be lost
