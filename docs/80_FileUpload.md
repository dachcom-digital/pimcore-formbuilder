# File Upload Type

<img width="566" src="https://user-images.githubusercontent.com/700119/30774631-1eaf7d22-a086-11e7-81d8-382e30a60eef.png">

FormBuilder comes with a smart multi file upload type. 
For a superb user experience, it's using the [fineUploader](https://github.com/FineUploader/fine-uploader) library.

## Workflow:
- File Upload per file type (yes, it's possible to place multiple upload fields per form)
- Chunked upload for large files
- Store uploaded file reference in session, since the upload works decoupled from form submission
- After a form submission, the data will be compressed and get stored in pimcore (`/formdata` asset folder)
- Append download link in mail
- Clean Up: unsubmitted data / chunk data will be swiped via maintenance

## Configuration
There are some options in the field configuration.

### Max File Size
Max file size will be calculated in MB. Empty or Zero means no Limit

### Allowed Extensions
Define allowed extensions, for example: `pdf, zip`.

## Implementation

### Styling

Add the CDN Link or implement your own styling.

```twig
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.15.0/all.fine-uploader/fine-uploader-new.min.css" />
```

To enable fineuploader, add the javascript library.

### Scripts
```twig
    <script type="text/javascript" src="{{ asset('bundles/formbuilder/js/frontend/plugins/jquery.fb.core.form-builder.js') }}"></script>
```
