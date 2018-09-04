# Pimcore FormBuilder
Pimcore 5.x FormBuilder.

#### Requirements
* Pimcore 5.1+

#### Pimcore 4 
Get the Pimcore4 Version [here](https://github.com/dachcom-digital/pimcore-formbuilder/tree/pimcore4).

## Installation
```json
"require" : {
    "dachcom-digital/formbuilder" : "~2.4.0"
}
```

## Usage
Just open a document and place the form area brick like any other bricks via drag and drop. 
Use the edit button at the right top corner to configure your form. 
Also make sure you've included the [flash template](docs/11_SuccessMessage.md#flash-messages-implementation) if you want to have some success messages after a redirect.

It's also possible to render a form via Twig or even within a controller method. [Click here](docs/0_Usage.md) to learn more about the form rendering types.

## Overriding Templates
Nothing to tell here, it's just [Symfony](https://symfony.com/doc/current/templating/overriding.html) standard.

## Further Information
- [Usage (Rendering Types, Configuration)](docs/0_Usage.md)
- [Export Forms](docs/1_ExportForms.md)
- [Mail Template Configuration](docs/10_MailTemplates.md)
- [Success Message](docs/11_SuccessMessage.md)
- [Dynamic Fields](docs/20_AjaxForms.md)
- [Available Form Types](docs/30_FormTypes.md)
  - [Dynamic Choice Type](docs/82_DynamicChoice.md)
  - [Dynamic Multi File Type](docs/80_FileUpload.md)
- [Create Custom Form Type](docs/40_CustomFormType.md)
- [Custom Form Type Backend Layout](docs/50_CustomFormTypeBackendLayout.md)
- [Using Presets](docs/60_Presets.md)
- [Events](docs/70_Events.md)
- [Custom Fields with Events](docs/71_CustomFields.md)
- [Mastering File Uploads](docs/80_FileUpload.md)
- [Conditional Logic](docs/81_ConditionalLogic.md)
- [Frontend Tips](docs/90_FrontendTips.md)
- [FormBuilder Javascript Plugins](docs/91_Javascript.md)
- [Configuration Flags](docs/100_ConfigurationFlags.md)

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  
