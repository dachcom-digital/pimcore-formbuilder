# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  
***
After every update you should check the pimcore extension manager. Just click the "update" button to finish the bundle update.

#### Update from Version 2.x to Version 2.1.0
- "Mark field as required" has been removed. Please check your form and add a "not-blank" constraint to every required field!
- `formbuilder.js` has been moved to `js/frontend/legacy`. This file is now deprecated and will be removed in Version 3.0.0!
- `jquery.fine-uploader.js` has been moved to `js/frontend/vendor`. This file is now deprecated and will be removed in Version 3.0.0!

#### Update from Version 1.x to Version 2.0.0
- TBD