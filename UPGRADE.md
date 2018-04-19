# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  
***
After every update you should check the pimcore extension manager. Just click the "update" button to finish the bundle update.

#### Update from Version 2.2.x to Version 2.3.1
- **[NEW FEATURE]**: Add Placeholder to the *ReplyTo* Field

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