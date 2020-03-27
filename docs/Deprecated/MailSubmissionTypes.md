# Mail Submission Types

## Deprecated!
**Warning!** This feature has been deprecated within version 3.3. 
It still will be available for BC reasons but is not recommended anymore.
Please use the [Email Channel](../OutputWorkflow/10_EmailChannel.md) within the [Output Workflows](../OutputWorkflow/0_Usage.md) instead.

***

### HTML
FormBuilder always submits html mails. 
However, if you have installed the [html2text](http://www.mbayer.de/html2text/index.shtml) library, it will also append a text/plain version to each email.
With that, the email client is able to choose between html and plain text.

**Attention!** If the html2text library is not installed, the text/plain version will be skipped silently.

### Plain Text
In some cases you may want to force the text/plain submission. 
To do so, you need to set the [_mail_force_plain_text_ Property](./10_MailTemplates.md#mail_force_plain_text-checkbox).

**Attention!** Please note that you must have enabled the html2text library, otherwise an exception will be thrown at time of mail submission.

### Advanced HTML Templating
Use the [emailizr](https://github.com/dachcom-digital/pimcore-emailizr) library to generate simple structured html templates and inline styled markup out of the box.
This library does **not** comes with the default FormBuilder installation.