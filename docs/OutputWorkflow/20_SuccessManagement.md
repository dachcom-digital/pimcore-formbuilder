# Success Management
Each Output Workflow is able to process its own success workflow.

![image](https://user-images.githubusercontent.com/700119/77761369-3a7d6f80-7038-11ea-9044-10fd46965ecf.png)

### Simple Text 
If you're adding a text type, the form is just returning the text value you could use for a output.
"Thank you for your message" for example.

### Snippet
A Snippet could be useful if you need more output structure. FormBuild will render the snippet and return its html data.

### Redirect to Document
Use a document to get its path to perform a redirect for example.

### Flash Messages on Redirect
If you're using `Document` as a success handler you're able to define a flash message. 

To define a flash message you need to fill out the `Flash Message` below.

***

## Flash Messages Implementation
Be sure you have included the twig template somewhere on top in your main layout:

```twig
{% include '@FormBuilder/Common/redirect_flash_message.html.twig' %}
```

## Javascript Example
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