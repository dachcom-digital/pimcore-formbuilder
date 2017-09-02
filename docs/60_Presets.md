# Using Presets

Presets are here to set some additional data for your custom forms.

### Example:
Your customer want's to add a special value (mostly dynamic data) to one or more but specific forms he has just made. 
Since all forms are dynamically created, you don't know the specific ids where to add all those values.

Presets will help you here: Create a preset and add some description to it. 
From now on, your customer is able to choose between presets within the dropped area element. 

### Things to know
- If a preset gets selected, form builder will change the layout view to `Form/Presets/your_preset_name.html.twig` instead of `Form/default.html.twig`.
- [Create a EventListener](70_Events.md) and add some specific data depending on the selected preset - regardless of the configured form.

### Configuration

```yaml
form_builder:
    area:
        presets:
            -
            jobs: # keep it short and simple :)
                nice_name: 'Short Name'
                admin_description: 'Description of your Preset'
                sites:
                    - 'domain1.com'
                    - 'domain2.com'

```