# Dynamic Multi File

FormBuilder comes with a smart multi-file upload type.
It allows you to use different adapters/libraries like FineUploader or DropZoneJs.

## Highlights
- File Upload per file type (yes, it's possible to place multiple upload fields per form)
- Large File Support: Process chunked files to allow large file uploads
- Different adapters: Choose between different upload handler or create a custom one!  
- Stateless: no session is required to handle file uploads
- Different storage principals: Store data as pimcore assets (`/formdata` asset folder) and add a download-link to mail **or** add them as native mail attachments
- Stay clean: unsubmitted data / chunk data will be swiped via maintenance
- Prebuild Extensions: Use included jQuery extensions to set up your form in the front end in no time!

## Field Configuration
There are some options in the (backend) field configuration:

| Name                       | Description                                                                                                                                                                         |
|----------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Max File Size`            | Max file size will be calculated in MB. Empty or zero means no limit                                                                                                                |
| `Allowed Extensions`       | Define allowed extensions, for example: `pdf, zip` (Format depends on active adapter)                                                                                               |
| `Item limit`               | The maximum number of files that can be uploaded. Empty or zero means no limit                                                                                                      |
| `Send Files as Attachment` | All Files will be stored in your pimcore asset structure (/formdata) by default. If you check this option, the files will be attached to the mail instead of adding a download link |

## Setup
Per default, DropZone will be used. If you want to change the dmf adapter, you need to define it:

```yaml
form_builder:
    dynamic_multi_file_adapter: FormBuilderBundle\DynamicMultiFile\Adapter\DropZoneAdapter
```

## Front-End Setup
By default, you don't need to implement more than the standard initialization, described in [FormBuilder Javascript Core Extension](./91_Javascript.md).
The core extension will try to fetch the handler path, defined by `dynamicMultiFileHandlerOptions.defaultHandlerPath`.

## Security

### Field Reference

```yaml
form_builder:
    security:
        enable_upload_field_reference: true
```

The Field Reference feature ensures that every uploaded file is associated with an existing form field. 
When enabled, the client must send a reference to the corresponding form field along with the file. 
The server validates that the field exists and that the upload complies with the field’s configuration (e.g., allowed file types, maximum upload size).

> [!CAUTION]  
> This option is disabled by default to avoid breaking existing uploads. 
> Enable it when you want to enforce field-level validation.

### Server MIME Type Validation

```yaml
form_builder:
    security:
        enable_upload_server_mime_type_validation: true
```

Server MIME Type Validation enforces that uploaded files match the allowed MIME types defined on the server. 
When enabled, the server checks the actual file content rather than relying solely on file extensions provided by the client. 
This ensures that only valid file types are accepted, preventing spoofed files or uploads that do not match the allowed formats.

> [!CAUTION]  
>Important: When MIME type validation is active, you must list the allowed MIME types in your form field configuration.
> Users can no longer just specify file extensions (like `pdf` or `jpg`); the server will validate the real MIME type of the uploaded file (for example, `application/pdf` or `image/jpeg`).
> If the MIME type of the file is not included in the server configuration, the upload will be rejected, even if the file extension appears valid.
> Always ensure the MIME types in your configuration match the types you expect to allow for uploads.

> [!CAUTION]  
> Note: This option is disabled by default to maintain backward compatibility.
> Enable it when you want the server to strictly validate MIME types for uploaded files.

***

## Available Adapter
- [DropZoneJs](./DynamicMultiFile/01_DropZoneJs.md)
- [FineUploader](./DynamicMultiFile/02_FineUploader.md)
- [Custom Adapter](./DynamicMultiFile/99_CustomAdapter.md)