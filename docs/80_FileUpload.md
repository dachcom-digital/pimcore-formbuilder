# Dynamic Multi File

FormBuilder comes with a smart multi file upload type.
It allows you to use different adapters/libraries like FineUploader or DropZoneJs.

## Highlights
- File Upload per file type (yes, it's possible to place multiple upload fields per form)
- Large File Support: Process chunked files to allow large file uploads
- Different adapters: Choose between different upload handler or create a custom one!  
- Stateless: no session is required to handle file uploads
- Different storage principals: Store data as pimcore assets (`/formdata` asset folder) and add download-link to mail **or** add them as native mail attachments
- Stay clean: unsubmitted data / chunk data will be swiped via maintenance
- Prebuild Extensions: Use included jQuery extensions to set up your form in front end in no time!

## Field Configuration
There are some options in the (backend) field configuration:

| Name | Description
|------|------------|
| `Max File Size` | Max file size will be calculated in MB. Empty or zero means no limit |
| `Allowed Extensions` | Define allowed extensions, for example: `pdf, zip` (Format depends on active adapter) |
| `Item limit` | The maximum number of files that can be uploaded. Empty or zero means no limit |
| `Send Files as Attachment` | All Files will be stored in your pimcore asset structure (/formdata) by default. If you check this option, the files will be attached to the mail instead of adding a download link |

## Setup
Per default, FineUploader will be used. If you want to change the dmf adapter, you need to define it:

```yaml
form_builder:
    dynamic_multi_file_adapter: FormBuilderBundle\DynamicMultiFile\Adapter\DropZoneAdapter
```

## Front-End Setup
By default, you don't need to implement more than the standard initialization, described in [FormBuilder Javascript Core Extension](./91_Javascript.md#core-extension).
The core extension will try to fetch the handler path, defined by `dynamicMultiFileDefaultHandlerPath`.

All handler will be initialized by lazy loading, so they will be requested only if upload files are available. 
However, if you **don't** want to initialize any handler because of your own frontend logic for example, you may want to disable the initialization:

```javascript
$('form.formbuilder.ajax-form').formBuilderAjaxManager({
    setupFileUpload: false, // disable default dynamic multi file handler
});
```

## Available Adapter
- [DropZone.Js](./DynamicMultiFile/01_DropZoneJs.md)
- [FineUploader](./DynamicMultiFile/02_FineUploader.md)
- [Custom Adapter](./DynamicMultiFile/99_CustomAdapter.md)