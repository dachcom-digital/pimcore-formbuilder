# Configuration Flags

| Name | Type | Default
|------|------|------------|
| `use_custom_radio_checkbox` | bool | true |
| `use_honeypot_field` | bool | true |

***

## 🚩 use_custom_radio_checkbox flag
Change `use_custom_radio_checkbox` to false, if you don't want to use the bootstrap [custom forms](https://symfony.com/doc/current/form/bootstrap4.html#custom-forms).

```yaml
form_builder:
    flags:
        use_custom_radio_checkbox: false
```

## 🚩 use_honeypot_field flag
Change `use_honeypot_field` to false, if you don't want a honeypot field added to your forms.

```yaml
form_builder:
    flags:
        use_honeypot_field: false
```
