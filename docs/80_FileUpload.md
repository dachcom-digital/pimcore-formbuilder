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

### Upload Policy Validator
The Upload Policy Validator allows projects to define custom security and policy rules for file uploads, such as IP-based rate limits or user-specific upload rules.

The bundle itself contains no validation logic or infrastructure dependencies (e.g., cache, Redis, database).
Projects provide their own implementation and register it via bundle configuration.

#### Example: IP-Based Rate Limiting
A common use case is limiting the number of uploads per IP address within a certain time window.
This can be implemented easily using the Symfony RateLimiter component.

To keep the limiter lightweight, in this example we use a dedicated APCu cache service.


```yaml
form_builder:
    security:
        upload_policy_validator: App\Formbuilder\PolicyValidator\UploadedFilePolicyValidator
```

> [!NOTE]  
> The service must implement the interface `FormBuilderBundle\Validator\Policy\UploadPolicyValidatorInterface`.

This configuration defines a sliding-window rate limiter that 
allows a maximum of 10 uploads per 5 minutes per client:

```yaml
# config/packages/rate_limiter.yaml:
framework:
    rate_limiter:
        form_builder_upload:
            policy: sliding_window
            limit: 10
            interval: '5 minutes'
            cache_pool: cache.form_builder_upload_rate_limiter

services:
    cache.form_builder_upload_rate_limiter:
        class: Symfony\Component\Cache\Adapter\ApcuAdapter
        arguments:
            - 'form_builder_upload'
            - 0 # TTL is set to 0 so entries are not automatically evicted. The RateLimiter manages expiration internally.
```

```php
namespace App\Formbuilder\PolicyValidator;

use FormBuilderBundle\Validator\Policy\UploadPolicyValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use FormBuilderBundle\Stream\Upload\UploadedFileInterface;

class UploadedFilePolicyValidator implements UploadPolicyValidatorInterface
{
    public function __construct(private RateLimiterFactory $formBuilderUploadLimiter)
    {}

    public function validate(UploadedFileInterface $file, ?Request $request = null): void
    {
        $limiter = $this->formBuilderUploadLimiter->create($request->getClientIp());
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(
                null,
                'Rate limit exceeded. Please wait before uploading more files.'
                null,
                429
            );
        }
    }
}
```

***

## Available Adapter
- [DropZoneJs](./DynamicMultiFile/01_DropZoneJs.md)
- [FineUploader](./DynamicMultiFile/02_FineUploader.md)
- [Custom Adapter](./DynamicMultiFile/99_CustomAdapter.md)