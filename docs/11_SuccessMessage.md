# Success Message

To define a success message you need to add a `mail_successfully_sent` property to the **main** email template 
which you have defined in the previous placed form area.

> The `mail_successfully_sent` is automatically available after you've installed FormBuilder.
> By default it's a document property. If you just want to add a simple text phrase 
> you need to remove the property and re-add it as a text property.

*String*  
If you're adding a text type, the form is just returning the text value you could use for a output.
"Thank you for your message" for example.

*Snippet*  
A Snippet could be useful if you need more output structure. FormBuild will render the snippet and return its html data.

*Document*  
Use a document to get its path to perform a redirect for example.

## Flash Messages on Redirect
If you're using `Document` as a success handler you're able to define a flash message. 

To define a flash message you need to add the `mail_successfully_sent_flash_message` property to the **main** email template
which you have defined in the previous placed form area.

> The `mail_successfully_sent_flash_message` is automatically available after you've installed FormBuilder.

It's also possible to modify those success handler via conditional logic.

## Conditional Logic
It's also possible to change the success message based on different conditions. 
Read more about conditional logic [here](81_ConditionalLogic.md).

## Example
This is how your javascript listener could look like:

```javascript
$('form.ajax-form').on('formbuilder.success', 
    function (ev, message, redirect, $form) {
        if (redirect) {
             document.location.href = redirect;
        } else {
            alert(message[0]['message']);
        }
    }
);
```