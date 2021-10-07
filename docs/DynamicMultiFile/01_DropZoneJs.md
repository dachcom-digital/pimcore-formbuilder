# Dynamic Multi File | DropZone

![image](https://user-images.githubusercontent.com/700119/119269406-daf90080-bbf7-11eb-9059-01485bf2edf7.png)

## Enable Handler

```yaml
form_builder:
    dynamic_multi_file_adapter: FormBuilderBundle\DynamicMultiFile\Adapter\DropZoneAdapter
```

## Configure Library
Learn how to configure this extension in frontend [here](https://github.com/dachcom-digital/jquery-pimcore-formbuilder/blog/master/docs/11_dmf_fine_uploader.md).

## Field Configuration Notes
With DropZone you need to use the mime type in `Allowed Extensions` configuration. Example: `image/jpeg,image/png,image/gif,image/jpg,application/pdf` 