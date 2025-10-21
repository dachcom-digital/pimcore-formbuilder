# Upgrade Notes

## 5.3.4
- **[SECURITY]** Introduce upload field reference and server-side MIME type validation. Read more about upload security [here](./docs/80_FileUpload.md#security)
- **[SECURITY]** introduce policy validation for uploads. Read more about it [here](./docs/80_FileUpload.md#upload-policy-validator)
- **[IMPROVEMENT]** Handle invalid runtime data JSON in DynamicFormType with proper exception [@hitsoul11](https://github.com/dachcom-digital/pimcore-formbuilder/pull/525)

## 5.3.3
- **[BUGFIX]** Sanitize form field values by removing template tags during output transformation

## 5.3.2
- **[BUGFIX]** Validate and cast formId to integer in RequestListener

## 5.3.1
- **[BUGFIX]** [FUNNEL] Add channel context handling to FunnelWorker [#518](https://github.com/dachcom-digital/pimcore-formbuilder/issues/518)
- **[BUGFIX]** [FUNNEL] Render `_token` in funnel base template if available and renderable

## 5.3.0
- **[IMPROVEMENT]** New field added to `form_builder_form_attributes`: `autocomplete`
- **[BUGFIX]** Solidify check for empty value in output transformer [#486](https://github.com/dachcom-digital/pimcore-formbuilder/issues/508)
- **[SECURITY FEATURE]** Math CAPTCHA Field [#511](https://github.com/dachcom-digital/pimcore-formbuilder/issues/511), read more about it [here](./docs/03_SpamProtection.md#math-captcha)
- **[NEW FEATURE]** Form Import Options [#466](https://github.com/dachcom-digital/pimcore-formbuilder/issues/466)

## 5.2.0
- **[LICENSE]** Dual-License with GPL and Dachcom Commercial License (DCL) added
- **[NEW FEATURE]** Bootstrap 5 Layout Support [@mackrais](https://github.com/dachcom-digital/pimcore-formbuilder/pull/500)
- **[NEW FEATURE]** Introduce Channel Context [#486](https://github.com/dachcom-digital/pimcore-formbuilder/issues/486)
- **[IMPROVEMENT]** Doctrine ORM 3.0 Support [#503](https://github.com/dachcom-digital/pimcore-formbuilder/pull/503)
- **[BUGFIX]** API Channel: Keep array index when merging child nodes [@simon-matt-oetztal](https://github.com/dachcom-digital/pimcore-formbuilder/pull/496)
- **[BUGFIX]** Update Custom Channel Documentation [#493](https://github.com/dachcom-digital/pimcore-formbuilder/issues/493)
- **[BUGFIX]** Fix passing null value to `preg_split` 

## 5.1.4
- **[BUGFIX]** Allow using double-opt-in variables in placeholder processor

## 5.1.3
- **[BUGFIX]** Fix DoubleOptInType Service Definition [@dpfaffenbauer](https://github.com/dachcom-digital/pimcore-formbuilder/pull/483)

## 5.1.2
- **[IMPROVEMENT]** [File Upload] Add Deeplink Option to file upload field [#475](https://github.com/dachcom-digital/pimcore-formbuilder/issues/475)
- **[BUGFIX]** [Double-Opt-In] Remove unique index, allow admin to define unique data behavior [#477](https://github.com/dachcom-digital/pimcore-formbuilder/issues/477)
- **[IMPROVEMENT]** [Double-Opt-In] Allow to list and manage active sessions [#478](https://github.com/dachcom-digital/pimcore-formbuilder/issues/478)
- **[IMPROVEMENT]** [Double-Opt-In] Pass data to email channel and store double-opt-in main data in mail params [#479](https://github.com/dachcom-digital/pimcore-formbuilder/issues/479)

## 5.1.1
- **[BUGFIX]** Fix Migration and Installer

## 5.1.0
- **[SECURITY FEATURE]** Double-Opt-In Feature, read more about it [here](./docs/04_DoubleOptIn.md)
- **[SECURITY FEATURE]** Email Checker Validator [#471](https://github.com/dachcom-digital/pimcore-formbuilder/issues/471), read more about it [here](./docs/03_SpamProtection.md#email-checker)
  - If you're using a custom form theme, please include the `instructions` type (`{% use '@FormBuilder/form/theme/type/instructions.html.twig' %}`)
- **[SECURITY FEATURE]** Add [friendly captcha field](/docs/03_SpamProtection.md#friendly-captcha)
- **[SECURITY FEATURE]** Add [cloudflare turnstile](/docs/03_SpamProtection.md#cloudflare-turnstile)
- **[BUGFIX]** Use Pimcore AdminUserTranslator for Editable Dialog Box [#450](https://github.com/dachcom-digital/pimcore-formbuilder/issues/450)
- **[BUGFIX]** CSV Export: Ignore mail params with empty data [#461](https://github.com/dachcom-digital/pimcore-formbuilder/issues/461)
- **[IMPROVEMENT]** Improve response message context [#416](https://github.com/dachcom-digital/pimcore-formbuilder/issues/416)
- **[IMPROVEMENT]** Improve API OC Field Mapping [#462](https://github.com/dachcom-digital/pimcore-formbuilder/issues/462)
- **[IMPROVEMENT]** Improve json response success message behaviour [#416](https://github.com/dachcom-digital/pimcore-formbuilder/issues/416)
- **[IMPROVEMENT]** Allow custom message in `DynamicMultiFileNotBlankValidator` constraint [#438](https://github.com/dachcom-digital/pimcore-formbuilder/issues/438)
- **[IMPROVEMENT]** [#458](https://github.com/dachcom-digital/pimcore-formbuilder/pull/458)
  - Allow to modify FormType options via `FORM_TYPE_OPTIONS` event
  - Do not render `formRuntimeDataToken` if csrf has been disabled in form options 
  - Allow form assembling without request and view resolver
  - Add FormDialogBuilder

## 5.0.7
- Remove `editable_root` restriction from mail editor
- Skip widget field rendering, if no label and no value is available
- Use TranslatorInterface instead of Pimcore Translator [@dpfaffenbauer](https://github.com/dachcom-digital/pimcore-formbuilder/pull/446)
- fix type error in finishWithSuccess [@jheimbach](https://github.com/dachcom-digital/pimcore-formbuilder/pull/445)

## 5.0.6
- Fix magic property access [#442](https://github.com/dachcom-digital/pimcore-formbuilder/issues/442)

## 5.0.5
- Sort chunked uploaded files before merging

## 5.0.4
- Add Additional HrefTransformer validation for `$type` and `$id` [@patkul0](https://github.com/dachcom-digital/pimcore-formbuilder/pull/434)
- Fix chunked upload merge [@life-style-de](https://github.com/dachcom-digital/pimcore-formbuilder/pull/430)

## 5.0.3
- Fix element type check in api channel [#423](https://github.com/dachcom-digital/pimcore-formbuilder/issues/423)

## 5.0.2
- Fix Mail Layout Editor base path [#426](https://github.com/dachcom-digital/pimcore-formbuilder/issues/426)

## 5.0.1
- Fix Mail Layout Editor Availability State [#420](https://github.com/dachcom-digital/pimcore-formbuilder/issues/420)

***

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
