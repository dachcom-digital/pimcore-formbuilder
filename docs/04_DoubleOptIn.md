# Double-Opt-In
![image](https://github.com/user-attachments/assets/aa4f1f24-607c-4ed3-aa72-2d9d91fddf12)

When enabled, a user must confirm its email identity via confirmation before the real form shows up.

This feature is disabled by default.

```yaml
form_builder:

    double_opt_in:
        
        # enable the feature
        enabled: true
        
        # redeem_mode:
        # choose between "delete" or "devalue"
        # - "delete" (default): The double-opt-in session token gets deleted, after the form submission was successful
        # - "devalue": The double-opt-in session token only gets redeemed but not deleted, after the form submission was successful. 
        redeem_mode: 'delete'
        
        expiration:
            # delete open sessions after 24 hours (default). If you set it to 0, no sessions will be deleted ever.
            open_sessions: 24
            # delete redeemed session after x hours (default 0, which means: disabled)
            redeemed_sessions: 0
```

## Extending Double-Opt-In Form
By default, the `DoubleOptInType` form type only contains a `emailAddress` field to keep users effort small.
If you want to extend the form, you may want to use a symfony [form extension](https://symfony.com/doc/current/form/create_form_type_extension.html).

Additional Info:
- `emailAddress` is required and you're not allowed to remove it
- Additional fields will be stored as array in the DoubleOptInSession in `additionalData`

## Trash-Mail Protection
The `EmailChecker` Validator is automatically appended to the `emailAddress` field.
This validator only triggers, if you've configured at least one email checker service - read more about it [here](./docs/03_SpamProtection.md#email-checker)

## Templating
Based on given output workflow, you may want to use the double opt in data in given channel:

### E-Mail Channel
If DOI is active, the submitted mail object will receive two additional parameters:
- _form_builder_double_opt_in_token
- _form_builder_double_opt_in_session_email

#### E-Mail Data Template
DOI information can't be rendered by default since the rendering heavily depends on your implementation. 
Checkout out this [part](https://github.com/dachcom-digital/pimcore-formbuilder/blob/a9da6dada95274049d07f920999b57dfc0c9b462/templates/email/form_data.html.twig#L57-L74) in `templates/email/form_data.html.twig`, 
to show DOI data within your submitted mail data.

#### E-Mail Editor
![image](https://github.com/user-attachments/assets/30f209a1-231a-4511-bdf9-0c6ccef423d3)
Use the additional fields on the right side to add DOI information to the mail template editor.

### Object Channel
Currently not implemented

### API Channel
Currently not implemented
