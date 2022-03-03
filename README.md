# Pimcore FormBuilder

[![Join the chat at https://gitter.im/pimcore/pimcore](https://img.shields.io/gitter/room/pimcore/pimcore.svg?style=flat-square)](https://gitter.im/pimcore/pimcore)
[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/formbuilder.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/formbuilder)
[![Tests](https://img.shields.io/github/workflow/status/dachcom-digital/pimcore-formbuilder/Codeception/master?style=flat-square&logo=github&label=codeception)](https://github.com/dachcom-digital/pimcore-formbuilder/actions?query=workflow%3ACodeception+branch%3Amaster)
[![PhpStan](https://img.shields.io/github/workflow/status/dachcom-digital/pimcore-formbuilder/PHP%20Stan/master?style=flat-square&logo=github&label=phpstan%20level%204)](https://github.com/dachcom-digital/pimcore-formbuilder/actions?query=workflow%3A"PHP+Stan"+branch%3Amaster)

![PIMCORE FormBuilder](https://user-images.githubusercontent.com/700119/137106375-3618b401-c2cd-4c56-8c29-179f12e6a94f.png)

### Release Plan

| Release | Supported Pimcore Versions        | Supported Symfony Versions | Release Date | Maintained     | Branch     |
|---------|-----------------------------------|----------------------------|--------------|----------------|------------|
| **4.x** | `10.1` - `10.3`                   | `5.3`                      | 13.10.2021   | Feature Branch | master     |
| **3.x** | `6.0` - `6.9`                     | `3.4`, `^4.4`              | 17.07.2019   | Bugfix only    | [3.x](https://github.com/dachcom-digital/pimcore-formbuilder/tree/3.x) |
| **2.7** | `5.4`, `5.5`, `5.6`, `5.7`, `5.8` | `3.4`                      | 27.06.2019   | Unsupported    | [2.7](https://github.com/dachcom-digital/pimcore-formbuilder/tree/2.7) |
| **1.5** | `4.0`                             | --                         | 18.03.2017   | Unsupported    | [pimcore4](https://github.com/dachcom-digital/pimcore-formbuilder/tree/pimcore4) |

## Installation

```json
"require" : {
    "dachcom-digital/formbuilder" : "~4.1.0"
}
```

- Execute: `$ bin/console pimcore:bundle:enable FormBuilderBundle`
- Execute: `$ bin/console pimcore:bundle:install FormBuilderBundle`

## Upgrading
- Execute: `$ bin/console doctrine:migrations:migrate --prefix 'FormBuilderBundle\Migrations'`

## Usage
![](http://g.recordit.co/39nEX5OhQK.gif)
1. Go to `Settings` => `Form Builder Settings` and create your form (Make sure your [spam protection](docs/03_SpamProtection.md) is covered).
2. Open a document and place the form area brick like any other bricks via drag and drop. 
3. Use the edit button at the right top corner to configure your form. 

Also make sure you've included the [flash template](docs/OutputWorkflow/20_SuccessManagement.md#flash-messages-implementation) if you want to have some success messages after a redirect.
It's also possible to render a form via Twig or even within a controller method. [Click here](docs/0_Usage.md) to learn more about the form rendering types.

## Overriding Templates
Nothing to tell here, it's just [Symfony](https://symfony.com/doc/current/templating/overriding.html) standard.

## Further Information
- [SPAM Protection (Honeypot, reCAPTCHA)](docs/03_SpamProtection.md)
- [Usage (Rendering Types, Configuration)](docs/0_Usage.md)
- [Output Workflows](docs/OutputWorkflow/0_Usage.md)
- [Output Workflows](docs/OutputWorkflow/0_Usage.md)
  - [API Channel](docs/OutputWorkflow/09_ApiChannel.md)
  - [Email Channel](docs/OutputWorkflow/10_EmailChannel.md)
  - [Object Channel](docs/OutputWorkflow/11_ObjectChannel.md)
  - [Custom Channel](docs/OutputWorkflow/12_CustomChannel.md)
  - [Output Transformer](docs/OutputWorkflow/15_OutputTransformer.md)
  - [Field Transformer](docs/OutputWorkflow/16_FieldTransformer.md)
  - [Success Management](docs/OutputWorkflow/20_SuccessManagement.md)
- [Backend Administration of Forms](docs/01_BackendUsage.md)
- [Export Forms](docs/02_ExportForms.md)
- [Ajax Forms](docs/20_AjaxForms.md)
- [Dynamic Fields (Add form elements via events)](docs/71_DynamicFields.md)
  - [Dynamic Fields with Ajax Forms](docs/72_DynamicFieldsWithAjax.md)
- [Available Form Types](docs/30_FormTypes.md)
  - [Dynamic Choice Type](docs/82_DynamicChoice.md)
  - [Dynamic Multi File Type](docs/80_FileUpload.md)
  - [Container Type](docs/84_ContainerType.md)
- [Create Custom Form Type](docs/40_CustomFormType.md)
- [Custom Form Type Backend Layout](docs/50_CustomFormTypeBackendLayout.md)
- [Form Presets](docs/60_Presets.md)
- [Events](docs/70_Events.md)
- [Mastering File Uploads](docs/80_FileUpload.md)
  - [DropZoneJs](docs/DynamicMultiFile/01_DropZoneJs.md)
  - [FineUploader](docs/DynamicMultiFile/02_FineUploader.md)
  - [Custom Adapter](docs/DynamicMultiFile/99_CustomAdapter.md)
- [Conditional Logic](docs/81_ConditionalLogic.md)
- [Form & Field Attributes](docs/83_Attributes.md)
- [Frontend Tips](docs/90_FrontendTips.md)
- [FormBuilder Javascript Plugins](docs/91_Javascript.md)
- [Configuration Flags](docs/100_ConfigurationFlags.md)

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  
