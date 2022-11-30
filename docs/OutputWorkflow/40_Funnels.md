## Funnels
Funnels are disabled by default.

## Enable Funnel Feature

```yaml
form_builder:
    funnel:
        enabled: true
```

## Add Routes
Import preconfigured funnel routes from the FormBuilder package or copy them into your project.

```yaml
# config/routes.yaml
form_builder_routing_funnels:
    resource: '@FormBuilderBundle/Resources/config/pimcore/routing_funnels.yml'

# or import routes without the {locale} flag in routes
# form_builder_routing_funnels:
#     resource: '@FormBuilderBundle/Resources/config/pimcore/routing_funnels_not_localized.yml'
```
