# Output Workflows

![image](https://user-images.githubusercontent.com/700119/77752709-5aa53280-7028-11ea-9615-1165859c08ed.png)

The Output Workflows allows you to define powerful finishing workflows, after a form has been successfully submitted.
To give you a slight overview of the idea behind all this, we'll show you some scenarios you may want to achieve:

### Default Scenario
You simply want to send all the form data as email to a given admin and to the user for confirmation.

- Create Output Workflow
    - Add a new Email-Channel and select a valid pimcore mail template
    - Add another Email-Channel and select a valid pimcore mail template 
        - **Note**: Using the `%FIELD_NAME%` placeholders in the `to` field for example is still valid!) 
        
### Extended Scenario I
You want to offer an event registration form. After a client has submitted the form,
you want to send him a confirmation email and store the data in a structured DataObject called `Event`.

- Create Output Workflow
    - Add a new Email-Channel and select a valid pimcore mail template
    - Add a new Object-Channel and select the resolver strategy
    - Map your form fields with all the object fields of `Event`
    
### Extended Scenario II
You want to offer an event registration form. After a client has submitted the form,
you want to send him a confirmation email and submit all the data to some external services via API. 

- Create Output Workflow
    - Add a new Email-Channel and select a valid pimcore mail template
    - Create a [Custom Channel](./12_CustomChannel.md)
    
As you can see, these are powerful tools which allows you to define workflows without writing a single line of code.
Want to learn more? Let's start with the [Email Channel](./10_EmailChannel.md).

## Output Workflow Topics
  - [API Channel](./09_ApiChannel.md)
  - [Email Channel](./10_EmailChannel.md)
  - [Object Channel](./11_ObjectChannel.md)
  - [Custom Channel](./12_CustomChannel.md)
  - [Output Transformer](./15_OutputTransformer.md)
  - [Success Management](./20_SuccessManagement.md)
  - [Events](./30_Events.md)
    