# Mail Templates

Place your form somewhere on your Website.
If you want to submit the form to the user, you can use your field names as placeholders. Formbuilder automatically will transform the field into the given address.
For Example you can set a placeholder called `%emailaddress%` (where *emailaddress* is the name of your form field) in the *To:* field ("Settings" Tab of your email template).

**Subject, From, ReplyTo**
You also may want to add some placeholder in the subject of your mail template.
To do so, just add a placeholder like `%emailaddress%` to the subject field. They will get transformed automatically.

### Available Properties

Add those properties to your mail template.

#### mail_successfully_sent *(String|Document|Snippet)*

> Read more about the success message behaviour [here](11_SuccessMessage.md).

Use the `mail_successfully_sent` property to define a message after the form has been successfully sent.
There are three options:

*String*  
Use a String to return a simple text like "Thank you for your message".

*Snippet*  
Use a Snippet to return some complex html stuff.

*Document*  
Add a Document to redirect after your form has been successfully submitted.

#### mail_disable_default_mail_body *(Checkbox)*

If the `mail_disable_default_mail_body` property is defined and checked, you need to add your own data to the mail template.
You can use all the field names as placeholder. For example:

```html
Hello %Text(firstname);!

Your Data:

Name: %Text(firstname);
Mail: %Text(mailaddress);
```

#### mail_ignore_fields *(String)*

In some cases, you don't want to send specific fields via mail. For example, if you send a copy to the user.
To do so, just define the `mail_ignore_fields` property in your email template. Add one or multiple (comma separated) fields as string.
*Notice:* The field name needs be identical to the field name in your form configuration.

## Conditional Logic
It's also possible to change the email template identification based on different conditions. 
Read more about conditional logic [here](81_ConditionalLogic.md).