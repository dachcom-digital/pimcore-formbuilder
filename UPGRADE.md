# Upgrade Notes

### Update from Version 1.2.7 to Version 1.3
- Rename all forms in your Database. Search for `%formName%` and replace in data: `formname` => `form id`. 
- Remove all versions to prevent wrong data restoring.
- Install `website/var/config/formbuilder_configurations.php`
- Remove `name` unique index in database table `formbuilder_forms`
- All ini configuration files in `var/formbuilder/form/form_*.ini has been removed (see #4). remove this folder.
- All csv translation files in `formbuilder/lang/form_*.csv` will now be stored as json (see #24), so update your form (yes, you have to re-resave all your forms) and remove all those nasty csv files.
- Empty trash
- Clear cache