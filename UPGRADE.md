# Upgrade Notes

#### Update from Version 1.3.x to Version 1.4
- we changed the ajax request url. If you're using your own javascript class, check `static/js/frontend/formbuilder.js` for more information.
- we implemented a shiny new static route. please add it to your `var/config/staticroutes.php`:

```php
1 => [
    "id" => 1,
    "name" => "formbuilder_ajax",
    "pattern" => "/(?:([\w+]*))\/formbuilder\/request\/([A-Za-z0-9._-]+)/",
    "reverse" => "/{%lang/}formbuilder/request/{%action}",
    "module" => "Formbuilder",
    "controller" => "ajax",
    "action" => "%action",
    "variables" => "lang,action",
    "defaults" => NULL,
    "siteId" => NULL,
    "priority" => 0,
    "creationDate" => 1461311671,
    "modificationDate" => 1483643879
]

```
#### Update from Version 1.2.7 to Version 1.3
- Rename all forms in your Database. Search for `%formName%` and replace in data: `formname` => `form id`. 
- Remove all versions to prevent wrong data restoring.
- Install `website/var/config/formbuilder_configurations.php`
- Remove `name` unique index in database table `formbuilder_forms`
- All ini configuration files in `var/formbuilder/form/form_*.ini has been removed (see #4). remove this folder.
- All csv translation files in `formbuilder/lang/form_*.csv` will now be stored as json (see #24), so update your form (yes, you have to re-resave all your forms) and remove all those nasty csv files.
- Empty trash
- Clear cache