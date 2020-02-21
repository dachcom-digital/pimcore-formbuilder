# Pimcore FormBuilder

[![Join the chat at https://gitter.im/pimcore/pimcore](https://img.shields.io/gitter/room/pimcore/pimcore.svg?style=flat-square)](https://gitter.im/pimcore/pimcore)
[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Release](https://img.shields.io/packagist/v/dachcom-digital/formbuilder.svg?style=flat-square)](https://packagist.org/packages/dachcom-digital/formbuilder)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/dachcom-digital/pimcore-formbuilder.svg?style=flat-square)](https://www.scrutinizer-ci.com/g/dachcom-digital/pimcore-formbuilder)
[![Travis](https://img.shields.io/travis/com/dachcom-digital/pimcore-formbuilder/master.svg?style=flat-square)](https://travis-ci.com/dachcom-digital/pimcore-formbuilder)
[![PhpStan](https://img.shields.io/badge/PHPStan-level%202-brightgreen.svg?style=flat-square)](#)

![FormBuilder](https://user-images.githubusercontent.com/700119/48312098-066fee80-e5aa-11e8-97d4-02fcfdf4e51e.png)

### Release Plan

| Release | Supported Pimcore Versions        | Supported Symfony Versions | Release Date | Maintained     | Branch     |
|---------|-----------------------------------|----------------------------|--------------|----------------|------------|
| **3.x** | `6.0` - `6.5`                     | `3.4`, `^4.0`              | 17.07.2019   | Feature Branch | dev-master |
| **2.7** | `5.4`, `5.5`, `5.6`, `5.7`, `5.8` | `3.4`                      | 27.06.2019   | Bugfix only    | 2.7        |
| **1.5** | `4.0`                             | --                         | 18.03.2017   | Unsupported    | pimcore4   |

## Installation

```json
"require" : {
    "dachcom-digital/formbuilder" : "~3.2.0"
}
```

### Installation via Extension Manager
After you have installed the FormBuilder Bundle via composer, open pimcore backend and go to `Tools` => `Extension`:
- Click the green `+` Button in `Enable / Disable` row
- Click the green `+` Button in `Install/Uninstall` row

### Installation via CommandLine
After you have installed the FormBuilder Bundle via composer:
- Execute: `$ bin/console pimcore:bundle:enable FormBuilderBundle`
- Execute: `$ bin/console pimcore:bundle:install FormBuilderBundle`

## Upgrading

### Upgrading via Extension Manager
After you have updated the FormBuilder Bundle via composer, open pimcore backend and go to `Tools` => `Extension`:
- Click the green `+` Button in `Update` row

### Upgrading via CommandLine
After you have updated the FormBuilder Bundle via composer:
- Execute: `$ bin/console pimcore:bundle:update FormBuilderBundle`

### Migrate via CommandLine
Does actually the same as the update command and preferred in CI-Workflow:
- Execute: `$ bin/console pimcore:migrations:migrate -b FormBuilderBundle`

## Usage
![](http://g.recordit.co/39nEX5OhQK.gif)
1. Go to `Settings` => `Form Builder Settings` and create your form (Be sure your [spam protection](docs/03_SpamProtection.md) is covered!).
2. Open a document and place the form area brick like any other bricks via drag and drop. 
3. Use the edit button at the right top corner to configure your form. 

Also make sure you've included the [flash template](docs/11_SuccessMessage.md#flash-messages-implementation) if you want to have some success messages after a redirect.
It's also possible to render a form via Twig or even within a controller method. [Click here](docs/0_Usage.md) to learn more about the form rendering types.

## Overriding Templates
Nothing to tell here, it's just [Symfony](https://symfony.com/doc/current/templating/overriding.html) standard.

## Further Information
- [SPAM Protection (Honeypot, reCAPTCHA)](docs/03_SpamProtection.md)
- [Usage (Rendering Types, Configuration)](docs/0_Usage.md)
- [Backend Administration of Forms](docs/01_BackendUsage.md)
- [Export Forms](docs/02_ExportForms.md)
- [Mail Template Configuration](docs/10_MailTemplates.md)
  - [Mail Editor (new!)](docs/11_1_MailEditor.md)
- [Success Messages](docs/11_SuccessMessage.md)
- [Mail Submission Types (html/plain-text)](docs/12_MailSubmissionTypes.md)
- [Output Transformer (new!)](docs/13_OutputTransformer.md)
- [Dynamic Fields](docs/20_AjaxForms.md)
- [Available Form Types](docs/30_FormTypes.md)
  - [Dynamic Choice Type](docs/82_DynamicChoice.md)
  - [Dynamic Multi File Type](docs/80_FileUpload.md)
  - [Container Type](docs/83_ContainerType.md)
- [Create Custom Form Type](docs/40_CustomFormType.md)
- [Custom Form Type Backend Layout](docs/50_CustomFormTypeBackendLayout.md)
- [Form Presets](docs/60_Presets.md)
- [Events](docs/70_Events.md)
- [Custom Fields with Events](docs/71_CustomFields.md)
- [Mastering File Uploads](docs/80_FileUpload.md)
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
