# Upgrade Notes

#### Update to Version 4.1.2
- **[BUGFIX]**: Email CSV export: delete unnecessary line to prevent out of memory issues. [#328](https://github.com/dachcom-digital/pimcore-formbuilder/pull/328)

## Version 4.1.1
- **[BUGFIX]**: Fix legacy fine uploader identifier

## Version 4.1.0
- **[NEW FEATURE]**: API Output channel [#290](https://github.com/dachcom-digital/pimcore-formbuilder/issues/301)
- **[NEW FEATURE]**: API Output channel field transformer
- **[BUGFIX]**: ensure migration of form configs will be symfony5 compatible [@grizzlydotweb](https://github.com/dachcom-digital/pimcore-formbuilder/pull/310)
- **[BUGFIX]**: introduce output workflow signals: attachments not working with multiple channels [#311](https://github.com/dachcom-digital/pimcore-formbuilder/issues/311) 
- **[BUGFIX]**: fix typo in translation [#312](https://github.com/dachcom-digital/pimcore-formbuilder/issues/312)
- **[BUGFIX]**: disable `selectOnFocus` [#315](https://github.com/dachcom-digital/pimcore-formbuilder/issues/315)
- **[BUGFIX]**: ⚠️ add help text block to dynamic multi file. touched view: `form/theme/type/dynamic_multi_file.html.twig` [#308](https://github.com/dachcom-digital/pimcore-formbuilder/issues/308)
- **[BUGFIX]**: ⚠️ multi file upload: hidden type `_data` not in form row. touched view: `form/theme/type/dynamic_multi_file.html.twig` [#316](https://github.com/dachcom-digital/pimcore-formbuilder/issues/316) 

## Version 4.0.2
- [ENHANCEMENT] enable placeholder in cc and bcc field in email output workflow [@frithjof](https://github.com/dachcom-digital/pimcore-formbuilder/pull/305)

## Version 4.0.1
- [FEATURE] PIMCORE 10.2 Support
 
## Migrating from Version 3.x to Version 4.0.0
⚠️ If you're still on version `2.x`, you need to update to `3.x` first, then [migrate](https://github.com/dachcom-digital/pimcore-formbuilder/blob/3.x/UPGRADE.md) to `3.3`. After that, you're able to update to `^4.0`.

> 💀 Be careful while migrating to production!
> A lot of things (including form configuration) have changed and will break your installation if you're ignoring the migration guide below!

### Migration
- Execute `bin/console doctrine:migrations:migrate --prefix 'FormBuilderBundle\Migrations'` after you've installed FormBuilder
- Remove `var/bundles/FormBuilderBundle/forms` if migration was successful

### Global Changes
- Deprecations have been removed:
  - `FormBuilderBundle\Storage\Form` needs to be `FormBuilderBundle\Model\FormDefinition` now
  - `FormBuilderBundle\Storage\FormInterface` needs to be `FormBuilderBundle\Model\FormDefinitionInterface` now
  - `FormBuilderBundle\Storage\FormFieldSimpleInterface` needs to be `FormBuilderBundle\Model\FieldDefinitionInterface` now
  - `FormBuilderBundle\Manager\FormManager` needs to be `FormBuilderBundle\Manager\FormDefinitionManager` now
  - `FormBuilderBundle\Event\MailEvent` has been removed, use `FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent` instead
  - Method `FormBuilderBundle\Assembler\FormAssembler::setFormOptionsResolver` has been removed. `FormBuilderBundle\Assembler\FormAssembler::assembleViewVars($optionsResolver)` directly requires FormOptionsResolver now
  - CSV export types `Only Admin-Mail` and `Only User-Mail (Copy)` have been removed. Instead, you're now able to filter CSV export by available output workflows
  - Mail layout fallback feature has been removed (which was enabled if no workflows have been defined and have been stored in `formbuilder_forms.mailLayout`). Please migrate layouts to email channels. This column will be removed with FormBuilder 5.0
- PHP8 return type declarations added: you may have to adjust your extensions accordingly
- Email properties (`mail_successfully_sent`, `mail_ignore_fields`, `mail_force_plain_text`, `mail_disable_default_mail_body`) have been removed and won't be recognized anymore
- Area-Brick Configuration does not allow `sendMailTmplate` and `sendCopyMailTemplate` fallbacks anymore. They must be configured by output workflows now
- All Folders in `views` are lowercase/dashed now (`common/redirect-flash-message.html.twig`, `email/form-data.html.twig`, `form/elements/dynamic-multi-file`, `form/presets`, `form/theme`, ...)
- If you have a custom email template, make sure that you're passing the `body`, `editmode`, `document` attributes to your email view template (@see `\FormBuilderBundle\Controller::emailAction()`). Also use `{{ body|raw }}` instead of `%Text(body);` inside your view template!
- `DropZoneAdapter` has been declared to the new default Multi File Handler. You can switch back to FineUploader by changing the `form_builder.dynamic_multi_file_adapter` configuration node
- All frontend javascript libraries have been removed from core. If you still want to use the jQuery extensions, please check out our [jquery-pimcore-formbuilder repo](https://github.com/dachcom-digital/jquery-pimcore-formbuilder)

### Conditional Logic Changes
- ⚠️ Action `Change Mail Behaviour` has been removed. Use `Switch Output Workflow` Action instead. 

### Fixes
- Bootstrap Theme: Class `form-control` has been removed from dynamic multi file (see [#253](https://github.com/dachcom-digital/pimcore-formbuilder/pull/253)). You now need to set some [style definitions](docs/90_FrontendTips.md#multi-file-validation) by yourself
- Fixes root meta validation [#290](https://github.com/dachcom-digital/pimcore-formbuilder/issues/290)

### New Features
- Conditional Logic Action `Switch Output Workflow` added
- [Configurable Html2Text Options](./docs/OutputWorkflow/10_EmailChannel.md#configure-html2text-options)
- Yaml file storage migrated to Database storage
- Import/Export Improvement: You're able to export/import the complete form dataset (form, workflows and channels)

***

FormBuilder 3.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-formbuilder/blob/3.x/UPGRADE.md
