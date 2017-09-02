# Pimcore FormBuilder
Pimcore 5.0 FormBuilder.

#### Requirements
* Pimcore 5. Only with Build 96 or greater.

## Installation

```json
"require" : {
    "dachcom-digital/formbuilder" : "dev-master",
}
```

## Usage
Just open a document and place the form area brick like any other bricks via drag and drop. 
Use the edit button at the right top corner to configure your form.

## Misc

### CSS
There is a css example in `/Resources/public/css/formbuilder.css` (honeypot hide for example). 
Feel free to copy its content into your main style.

### Overriding Templates
Nothing to tell here, it's just [symfony](https://symfony.com/doc/current/templating/overriding.html) standard.

### Further Information
- [Mail Template Configuration](docs/10_MailTemplates.md)
- [Ajax Forms](docs/20_AjaxForms.md)
- [Available Form Types](docs/30_FormTypes.md)
- [Create Custom Form Type](docs/40_CustomFormType.md)
- [Custom Form Type Backend Layout](docs/50_CustomFormTypeBackendLayout.md)
- [Using Presets](docs/60_Presets.md)
- [Events](docs/70_Events.md)

## Upgrade Info
Before updating, please [check our upgrade notes!](UPGRADE.md)

## Copyright and license
Copyright: [DACHCOM.DIGITAL](http://dachcom-digital.ch)  
For licensing details please visit [LICENSE.md](LICENSE.md)  