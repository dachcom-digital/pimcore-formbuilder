# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  

***

After every update you should check the pimcore extension manager. 
Just click the "update" button or execute the migration command to finish the bundle update.

#### Update from Version 3.2.0 to Version 3.3.0
- **[IMPROVEMENT]**: Pimcore 6.6 ready
- **[IMPROVEMENT]**: Use doctrine ORM instead of DAO for Form Data
- **[IMPROVEMENT]**: Improve request management: Allow true `GET`, `HEAD`, `TRACE` submissions 
- **[IMPROVEMENT]**: Introduced FormDataInterface and FormDefinitionInterface to split submitted data from the definition itself. 
- **[IMPROVEMENT]**: RuntimeData Resolver added (The session based form configuration has been removed)
- **[IMPROVEMENT]**: Huge code base refactoring to improve symfony standards
- **[IMPROVEMENT]**: Implement Output Workflows [#114](https://github.com/dachcom-digital/pimcore-formbuilder/issues/114)
- **[DEPRECATION]**: Calling `\FormBuilderBundle\Assembler\FormAssembler::setFormOptionsResolver($optionBuilder);` has been marked as deprecated and will be removed with version 4.0. Pass the `$optionBuilder` directly via `\FormBuilderBundle\Assembler\FormAssembler::assembleViewVars($optionBuilder)`.
- **[DEPRECATION]**: `\Formbuilder\Storage\Form` and `\Formbuilder\Storage\FormInterface` has been marked as deprecated and will be removed with version 4.0. Use `\Formbuilder\Model\Form` and `\Formbuilder\Model\FormInterface` instead.
- **[DEPRECATION]**: `\Formbuilder\Manager\FormManager` has been marked as deprecated and will be removed with version 4.0. Use `\Formbuilder\Manager\FormDefinitionManager` instead.

#### Update from Version 3.2.0 to Version 3.2.1
- **[NEW FEATURE]**: Pimcore 6.5 ready

#### Update from Version 3.1.x to Version 3.2.0
- **[NEW FEATURE]**: Pimcore 6.4 ready
- **[NEW FEATURE]**: Make Honeypot field optional [@ihmels](https://github.com/dachcom-digital/pimcore-formbuilder/issues/167)
- **[NEW FEATURE]**: Allow global copy/paste of fields from form to form [@albertmueller](https://github.com/dachcom-digital/pimcore-formbuilder/pull/207)
- **[NEW FEATURE]**: Allow specific honeypot config [#212](https://github.com/dachcom-digital/pimcore-formbuilder/issues/212)
- **[NEW FEATURE]**: Implement reCAPTCHA v3 Field [#165](https://github.com/dachcom-digital/pimcore-formbuilder/issues/165)
- **[NEW FEATURE]**: Allow passing custom options in Twig- or Controller-Forms [#208](https://github.com/dachcom-digital/pimcore-formbuilder/issues/208)
- **[IMPROVEMENT]** html2text binary is not required anymore in pimcore >= 6.6 [#218](https://github.com/dachcom-digital/pimcore-formbuilder/issues/218)
- **[BUGFIX]**: Fix (multiple) dynamic choice data mapping [#205](https://github.com/dachcom-digital/pimcore-formbuilder/issues/205)

#### Update from Version 3.0.x to Version 3.1.0
- **[NEW FEATURE]**: Pimcore 6.3.0 ready

#### Update from Version 3.0.4 to Version 3.0.5
- **[BUGFIX]**: Check if checkbox configuration is available

#### Update from Version 3.0.3 to Version 3.0.4
- **[BUGFIX]**: [TRACKER] Check `window.dataLayer`

#### Update from Version 3.0.x to Version 3.0.3
- **[NEW FEATURE]**: Date-fields support choice for usage of html5 date-type
- **[BUGFIX]**: Fix dynamic choice type service detection

#### Update from Version 3.0.x to Version 3.0.2
- **[NEW FEATURE]**: [Tracker Extension](https://github.com/dachcom-digital/pimcore-formbuilder/issues/183)
- [Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/23?closed=1)

#### Update from Version 2.x to Version 3.0.0
- **[NEW FEATURE]**: Pimcore 6.0.0 ready
- **[BC BREAK]**: All Controllers are registered as services now! (Also check your email controller definition!)
- **[ATTENTION]**: All `href`, `multihref` elements has been replaced by `relation`, `relations`

***

FormBuilder 2.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-formbuilder/blob/2.7/UPGRADE.md