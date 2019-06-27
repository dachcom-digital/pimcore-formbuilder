# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  

***

After every update you should check the pimcore extension manager. 
Just click the "update" button or execute the migration command to finish the bundle update.

#### Update from Version 2.6.x to Version 2.7.0
- **[NEW FEATURE]**: [Mail Editor](https://github.com/dachcom-digital/pimcore-formbuilder/issues/158)
- **[NEW FEATURE]**: [Allow Plain Text Mail Submission](https://github.com/dachcom-digital/pimcore-formbuilder/issues/157)
- **[NEW FEATURE]**: [File As Attachment Option](https://github.com/dachcom-digital/pimcore-formbuilder/issues/156)
- [Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/20?closed=1)

#### Update from Version 2.6.x to Version 2.6.1
- **[NEW FEATURE]**: [Attributes in Container Fields](https://github.com/dachcom-digital/pimcore-formbuilder/issues/146)
- **[NEW FEATURE]**: [Allow HTML in Checkbox/Radio Label](https://github.com/dachcom-digital/pimcore-formbuilder/issues/111)
- [Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/19?closed=1)

#### Update from Version 2.5.x to Version 2.6.0
- **[BC BREAK]** (Js): `.formbuilder-row` Class added to each form element. `jquery.fb.ext.conditional-logic.js` now only listens to this class
- **[BC BREAK]** (Js): Validation allocation changed. Since we have sub forms now, we need the full form element id to validate against.
  If you're dealing with a custom error validation (`$(form).on('formbuilder.error-field, fn);` event for example), you need to adjust your field selector.
- **[BC BREAK]** (Template): Because of th new sub forms, we had to change the mail value formatter in `views/Email/formData.html.twig`. 
  Check your mail template, if you're using a custom template.
- **[NEW FEATURE]**: [Repeater Field](https://github.com/dachcom-digital/pimcore-formbuilder/issues/68)
- Various bug fixes ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/17?closed=1))

#### Update from Version 2.5.0 to Version 2.5.1
- **[IMPROVEMENT]**: Better Form Validation in ExtJs Context

#### Update from Version 2.4.2 to Version 2.5.0
- **[ATTENTION]**: Installer has moved to the [MigrationBundle](https://github.com/dachcom-digital/pimcore-formbuilder/issues/129). After updating to this version you need to enable this extension again!
- **[IMPROVEMENT]**: Pimcore 5.5 ready
- Various bug fixes and improvements ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/16?closed=1))

#### Update from Version 2.4.1 to Version 2.4.2
- fixed [issue #120](https://github.com/dachcom-digital/pimcore-formbuilder/issues/120) Fileupload failed with bigger files up to 2.5 MB
- implemented [PackageVersionTrait](https://github.com/pimcore/pimcore/blob/master/lib/Extension/Bundle/Traits/PackageVersionTrait.php)

#### Update from Version 2.4.0 to Version 2.4.1
- **[IMPROVEMENT]**: conditional logic jquery plugin: Fix class selector
- **[IMPROVEMENT]**: Pimcore 5.3 ready: implement csrf check
- Various bug fixes ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/14?closed=1))

#### Update from Version 2.3.4 to Version 2.4.0
- **[NEW FEATURE]**: Form mail export as CSV
- **[IMPROVEMENT]**: Fix choices translation bug
- **[IMPROVEMENT]**: Allow external page redirect in success workflow
- **[IMPROVEMENT]**: Use pure symfony custom radio/checkbox type, controllable by [configuration flag](https://github.com/dachcom-digital/pimcore-formbuilder/blob/master/docs/100_ConfigurationFlags.md).
- **[IMPROVEMENT]**: `userOptions` and `isCopy` Flag implemented in MailEvent
- Various bug fixes ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/13?closed=1))

#### Update from Version 2.3.3 to Version 2.3.4
- **[NEW FEATURE]**: [Flash Message](docs/11_SuccessMessage.md#flash-messages-on-redirect) in success redirect implemented
- **[IMPROVEMENT]**: Add Flash Message Namespace to allow dedicated form messages on page with multiple forms
- Various bug fixes ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/12?closed=1))

#### Update from Version 2.3.2 to Version 2.3.3
- **[NEW FEATURE]**: `item limit` in download field implemented
- **[NEW FEATURE]**: Conditional Logic: Mail Behaviour Switch
- **[NEW FEATURE]**: Conditional Logic: Success Message Switch
- Various bug fixes ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/11?closed=1))

#### Update from Version 2.3.1 to Version 2.3.2
- **[NEW FEATURE]**: Snippet in Conditional Logic is now website language sensitive
- Various bug fixes ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/10?closed=1))

#### Update from Version 2.2.x to Version 2.3.1
- **[NEW FEATURE]**: Add Placeholder to the *ReplyTo* Field
- Various bug fixes ([Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/9?closed=1)))

#### Update from Version 2.2.x to Version 2.3.0
- **[NEW FEATURE]**: *Date*, *DateTime*, *Time* and *Birthday* fields added
- **[NEW FEATURE]**: Configurable constraints and validation messages
- **[NEW FEATURE]**: *Length*, *Url*, *Regex*, *IP-Address*, *Range*, *Card Scheme*, *BIC*, *Iban* constraints added

#### Update from Version 2.1.x to Version 2.2.0
- **[DEPENDENCY]**: FormBuilder now requires Pimcore 5.1
- **[DEPENDENCY]**: We're using the [custom controls](https://getbootstrap.com/docs/4.0/components/forms/#custom-forms) functionality for checkboxes and radios (Bootstrap v4 Beta3).

#### Update from Version 2.x to Version 2.1.0
- **[NEW FEATURE]**: [Conditional Logic](docs/81_ConditionalLogic.md) implemented
- **[NEW FEATURE]**: Field *Country* Field added
- **[NEW FEATURE]**: Field *[Dynamic Choice Field](docs/82_DynamicChoice.md)* added
- **[NEW FEATURE]**: [jQuery Plugins](docs/91_Javascript.md) available
- **[BC BREAK]**: *"Mark field as required"* has been removed. Please check your form and add a "not-blank" constraint to every required field!
- **[BC BREAK]**: `formbuilder.js` has been moved to `bundles/formbuilder/js/frontend/legacy`. This file is now deprecated and will be removed in Version 3.0.0!
- **[BC BREAK]**: `jquery.fine-uploader.js` has been moved to `bundles/formbuilder/js/frontend/vendor`.

#### Update from Version 1.x to Version 2.0.0
- TBD
