# Upgrade Notes
 
## 5.0.3
- Fix element type check in api channel [#423](https://github.com/dachcom-digital/pimcore-formbuilder/issues/423)

## 5.0.2
- Fix Mail Layout Editor base path [#426](https://github.com/dachcom-digital/pimcore-formbuilder/issues/426)

## 5.0.1
- Fix Mail Layout Editor Availability State [#420](https://github.com/dachcom-digital/pimcore-formbuilder/issues/420)

## Migrating from Version 4.x to Version 5.0
- Execute: `bin/console doctrine:migrations:migrate --prefix 'FormBuilderBundle\Migrations'`

### Global Changes
- [DEPRECATION REMOVED] removed `FormDefinition::setMailLayout`. Please migrate to output workflows before updating
- [IMPROVEMENT] Recommended folder structure by symfony adopted
- [IMPROVEMENT] Make success flash message optional [#403](https://github.com/dachcom-digital/pimcore-formbuilder/issues/403)
- [IMPROVEMENT] Use name instead of ID in output workflow actions [#408](https://github.com/dachcom-digital/pimcore-formbuilder/pull/408)
- [FUNNEL] Route include changed from `@FormBuilderBundle/Resources/config/pimcore/routing_funnels.yml` to `@FormBuilderBundle/config/pimcore/routing_funnels.yaml`
- [BC BREAK] Mail Layout Editor: While there is a migration, we're not able to migrate container (fieldset, repeater) fields. Please adjust your output workflow channels manually.
- [BC BREAK] All views are lowercase/underscore now (`email/form_data.html.twig`, `form/elements/dynamic_multi_file/*`)


### New Features
- Mail Layout Editor, see [#390](https://github.com/dachcom-digital/pimcore-formbuilder/issues/398)
  - If [Emailizr](https://github.com/dachcom-digital/pimcore-emailizr) is installed, formbuilder will use it automatically to transform html tables

***

FormBuilder 4.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-formbuilder/blob/4.x/UPGRADE.md
