# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  

***

After every update you should check the pimcore extension manager. 
Just click the "update" button or execute the migration command to finish the bundle update.


#### Update from Version 3.0.x to Version 3.0.2
- **[NEW FEATURE]**: [Tracker Extension](https://github.com/dachcom-digital/pimcore-formbuilder/issues/183)
- [Milestone](https://github.com/dachcom-digital/pimcore-formbuilder/milestone/23?closed=1)

#### Update from Version 2.x to Version 3.0.0
- **[NEW FEATURE]**: Pimcore 6.0.0 ready
- **[BC BREAK]**: All Controllers are registered as services now! (Also check your email controller definition!)
- **[ATTENTION]**: All `href`, `multihref` elements has been replaced by `relation`, `relations`

***

FormBuilder 2.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-formbuilder/blob/2.7/UPGRADE.md