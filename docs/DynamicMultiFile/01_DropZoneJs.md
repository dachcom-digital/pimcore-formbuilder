# Dynamic MultiFile | DropZone

![image](https://user-images.githubusercontent.com/700119/119269406-daf90080-bbf7-11eb-9059-01485bf2edf7.png)

- Resource: https://www.dropzonejs.com 
- Handler: `jquery.fb.dmf.drop-zone.js`
- Library: [dropzone.min.js](https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js)

***

## Implementation

### Declarative Way
The simplest way to implement this adapter is by using the precompiled library loading by CDN and the corresponding handler.

```twig
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.css" />
<script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/plugins/jquery.fb.core.form-builder.js') }}"></script>
```

```javascript
$(function () {
    $('form.formbuilder.ajax-form').formBuilderAjaxManager({
        dynamicMultiFileHandlerOptions: {
            libPath: 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js'
        }
    });
});
```

#### Events
If you're using the default handler, you're able to hook into the most important initialization processes:

```javascript
$forms.on('formbuilder.dynamic_multi_file.init', function(ev, $dmfField, configuration) {
    // overwrite configuraiton
    configuration.addRemoveLinks = false;
});

$forms.on('formbuilder.dynamic_multi_file.drop_zone.init', function(ev, dropZoneInstance) {
    // add eventlistener
    dropZoneInstance.on('sending', function (file, xhr, formData) {
        console.log(file);
    });
});
```

### Declarative Way
This requires more work from your side since we only provide a simple jQuery Handler.
Read more about the implementation [here](https://dropzone.gitbook.io/dropzone/getting-started/setup/declarative). 
You also need to build your own handler and requires to **[disable the default behaviour](../80_FileUpload.md#disable-default-initialization)**.