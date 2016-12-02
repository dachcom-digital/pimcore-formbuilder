# Upgrade Notes

### Update from Version 1.2.7 to Version 1.3
- Rename all Forms in your Database. Search for `%formName%` and replace in data: `formname` => `form id`. 
- Remove all Versions to prevent wrong data restoring.
- Empty Trash
- Clear Cache
- Install `website/var/config/formbuilder_configurations.php`
- Remove `name` unique index in database table `formbuilder_forms`