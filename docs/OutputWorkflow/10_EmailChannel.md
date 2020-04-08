# Email Channel

![image](https://user-images.githubusercontent.com/700119/77756481-8677e680-702f-11ea-891a-bac498647b05.png)

Use the mail channel to submit your form data via email documents.

## Localization
You're able to define an email for every given locale, depending on your pimcore system settings.
There is also a `Default` fallback field. If a requested locale can't be found during submission, the default configuration will be used.

## Available Options

| Name | Type        | Description |
|------|-------------|-------------|
| Mail Template | Pimcore Mail Type | Use it to define the mail template via drag'n'drop. |
| Ignored Field in Email | Tags | In some cases, you don't want to send specific fields via mail. For example, if you send a copy to the user. Add one or multiple fields as string. *Notice:* The field name needs be identical to the field name in your form configuration. |
| Allow Attachment | Checkbox | If this is checked, attachments or attachments-links will be appended to the mail. |
| Force Plain Text Submission | Checkbox | If you want to force the plain text submission, you need to check this option. Read more about the submission types [below](./10_EmailChannel.md#mail-submission-types). Please be aware that this option requires the [html2text](http://www.mbayer.de/html2text/index.shtml) binary (But only if you're on Pimcore < 6.6). |
| Required By | Checkbox | If this is checked, you need to add your own data to the mail template. You can use all the field names as placeholder. This function is only necessary in rare cases. |

***

## Placeholder

Place your form somewhere on your Website.
If you want to submit the form to the user, you can use your field names as placeholders. Formbuilder automatically will transform the field into the given address.
For Example you can set a placeholder called `%emailaddress%` (where *emailaddress* is the name of your form field) in the *To:* field ("Settings" Tab of your email template).

**Subject, From, ReplyTo**
You also may want to add some placeholder in the subject of your mail template.
To do so, just add a placeholder like `%emailaddress%` to the subject field. They will get transformed automatically.

***

## Mail Submission Types

### HTML
FormBuilder always submits html mails. 
However, if you have installed the [html2text](http://www.mbayer.de/html2text/index.shtml) binary (Only required on Pimcore < 6.6), it will also append a text/plain version to each email.
With that, the email client is able to choose between html and plain text.

**Attention!** If the html2text binary is not installed (Only required on Pimcore < 6.6), the text/plain version will be skipped silently.

### Plain Text
In some cases you may want to force the text/plain submission. 
To do so, you need to set the [_mail_force_plain_text_ Property](./10_MailTemplates.md#mail_force_plain_text-checkbox).

**Attention!** Please note that you must have installed the html2text binary if you're on Pimcore < 6.6, otherwise an exception will be thrown at time of mail submission.

### Advanced HTML Templating
Use the [emailizr](https://github.com/dachcom-digital/pimcore-emailizr) library to generate simple structured html templates and inline styled markup out of the box.
This library does **not** comes with the default FormBuilder installation.

***

## Mail Layout Editor

![](http://g.recordit.co/OJ7uM6FxY0.gif)

> **Attention**: This mail layout editor does not respect any special mail template language (like inky)!
 
## Things to know
- Always save your form before opening the mail editor

## Custom Service
Use the mail editor to specify some special mail templates.
It's very easy to add some custom template widgets (Eg. date field).

### Register Service:

```yml
AppBundle\MailEditor\Widget\SpecialWidget:
    tags:
        - { name: form_builder.mail_editor.widget, type: special }
```

### Add PHP Service:
```php
<?php

namespace AppBundle\MailEditor\Widget;

use FormBuilderBundle\MailEditor\Widget\MailEditorWidgetInterface;

class DateWidget implements MailEditorWidgetInterface
{
    public function getWidgetGroupName()
    {
        return 'form_builder.mail_editor.widget_provider.others';
    }

    public function getWidgetLabel()
    {
        return 'form_builder.mail_editor.widget_provider.special.label';
    }

    public function getWidgetConfig()
    {
        // this config will appear in the editor if you double-click on your widget
        return [
            'link' => [
                'type'         => 'input',
                'defaultValue' => null,
                'label'        => 'form_builder.mail_editor.widget_provider.special.link'
            ],
        ];
    }

    public function getValueForOutput(array $config)
    {
        $form = $config['form'];
        $link = isset($config['link']) ? $config['link'] : '';

        return 'SPECIAL_DATA';
    }
}
```
