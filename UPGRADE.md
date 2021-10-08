# Upgrade Notes

## Migrating from Version 3.x to Version 4.0.0
⚠️ If you're still on version `2.x`, you need to update to `3.x` first, then [migrate](https://github.com/dachcom-digital/pimcore-formbuilder/blob/3.x/UPGRADE.md) to `3.3`. After that, you're able to update to `^4.0`.

> 💀 Be careful while migrating to production!
> A lot of things (including form configuration) have changed and will break your installation if you're ignoring the migration guide below!

### Global Changes
- Deprecations have been removed:
  - `FormBuilderBundle\Storage\Form` needs to be `FormBuilderBundle\Model\FormDefinition` now
  - `FormBuilderBundle\Storage\FormInterface` needs to be `FormBuilderBundle\Model\FormDefinitionInterface` now
  - `FormBuilderBundle\Storage\FormFieldSimpleInterface` needs to be `FormBuilderBundle\Model\FieldDefinitionInterface` now
  - `FormBuilderBundle\Manager\FormManager` needs to be `FormBuilderBundle\Manager\FormDefinitionManager` now
  - `FormBuilderBundle\Event\MailEvent` has been removed, use `FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent` instead
  - Method `FormBuilderBundle\Assembler\FormAssembler::setFormOptionsResolver` has been removed. `FormBuilderBundle\Assembler\FormAssembler::assembleViewVars($optionsResolver)` directly requires FormOptionsResolver now
- PHP8 return type declarations added: you may have to adjust your extensions accordingly
- Email-Properties (`mail_successfully_sent`, `mail_ignore_fields`, `mail_force_plain_text`, `mail_disable_default_mail_body`) have been removed and won't be recognized anymore
- Area-Brick Configuration does not allow `sendMailTmplate` and `sendCopyMailTemplate` fallbacks anymore. They must be configured by output workflows now
- All Folders in `views` are lowercase/dashed now (`common/redirect-flash-message.html.twig`, `email/form-data.html.twig`, `form/elements/dynamic-multi-file`, `form/presets`, `form/theme`, ...)
- If you have a custom email template, make sure that you're passing the `body`, `editmode`, `document` attributes to your email view template (@see `\FormBuilderBundle\Controller::emailAction()`). Also use `{{ body|raw }}` instead of `%Text(body);` inside your view template!
- `DropZoneAdapter` has been declared to the new default Multi File Handler. You can switch back to FineUploader by changing the `form_builder.dynamic_multi_file_adapter` configuration node

### Conditional Logic Changes
- ⚠️ Action `Change Mail Behaviour` has been removed. Use `Switch Output Workflow` Action instead. 

### Fixes
- Bootstrap Theme: Class `form-control` has been removed from dynamic multi file (see [#253](https://github.com/dachcom-digital/pimcore-formbuilder/pull/253)). You now need to set some [style definitions](docs/90_FrontendTips.md#multi-file-validation) by yourself

### New Features
- Conditional Logic Action `Switch Output Workflow` added
- [Configurable Html2Text Options](./docs/OutputWorkflow/10_EmailChannel.md#configure-html2text-options)

***

FormBuilder 3.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-formbuilder/blob/3.x/UPGRADE.md