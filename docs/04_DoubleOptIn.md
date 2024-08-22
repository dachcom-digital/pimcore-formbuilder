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
TBD