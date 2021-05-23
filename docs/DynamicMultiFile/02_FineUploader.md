# Dynamic Multi File | FineUploader

> Note! FineUploader has reached the [end of life](https://github.com/FineUploader/fine-uploader/issues/2073)
> and therefore we cannot longer maintain this adapter. It has been marked as deprecated since 3.4 and will be removed with 4.0.

![image](https://user-images.githubusercontent.com/700119/119269468-23b0b980-bbf8-11eb-8778-a43ad9a56088.png)

Resource: 

- Resource: https://fineuploader.com
- Handler: `jquery.fb.dmf.fine-uploader.js`
- Library: [fine-uploader.min.js](https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.16.2/fine-uploader.min.js)

***

## Implementation

#### Declarative Way
The simplest way to implement this adapter is by using the precompiled library loading by CDN and the corresponding handler.

```twig
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.css" />
<script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/plugins/jquery.fb.core.form-builder.js') }}"></script>
```

```javascript
$(function () {
    $('form.formbuilder.ajax-form').formBuilderAjaxManager({
        dynamicMultiFileHandlerOptions: {
            libPath: 'https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.16.2/fine-uploader.min.js'
        }
    });
});
```

#### Declarative Way
This requires more work from your side since we only provide a simple jQuery Handler.
Read more about the implementation [here](https://dropzone.gitbook.io/dropzone/getting-started/setup/declarative). 
You also need to build your own handler and requires to **[disable the default behaviour](../80_FileUpload.md#front-end-setup)**.