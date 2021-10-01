# Upgrade Notes

## Migrating from Version 3.x to Version 4.0.0
⚠️ If you're still on version `2.x`, you need to update to `3.x` first, then [migrate](https://github.com/dachcom-digital/pimcore-formbuilder/blob/3.x/UPGRADE.md) to `3.3`. After that, you're able to update to `^4.0`.

### Global Changes
- Deprecations have been removed:
  - `FormBuilderBundle\Storage\Form` needs to be `FormBuilderBundle\Model\FormDefinition` now
  - `FormBuilderBundle\Storage\FormInterface` needs to be `FormBuilderBundle\Model\FormDefinitionInterface` now
  - `FormBuilderBundle\Storage\FormFieldSimpleInterface` needs to be `FormBuilderBundle\Model\FieldDefinitionInterface` now
  - `FormBuilderBundle\Manager\FormManager` needs to be `FormBuilderBundle\Manager\FormDefinitionManager` now
- ⚠️ PHP8 return type declarations added: you may have to adjust your extensions accordingly
- 
FormBuilder 3.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-formbuilder/blob/3.x/UPGRADE.md