## Funnels
The Funnel-Feature allows you to create additional user journeys after the form has been submitted.

### Example
In this example, we've implemented a full checkout which allows users to buy goods which they have selected in the initial form builder form.

> 💰 **Attention!** The cart feature itself is not available under the open source licence!

Below you'll find a brief walk through to get the idea behind this powerful feature:

![image](https://user-images.githubusercontent.com/700119/207104368-133d754a-c404-4e62-bfb6-e753bcccb1ad.png)

1. The first Funnel Layer renders a Summary Page (_"Checkout Summary Layer"_) by using a custom snippet. There are two actions: 
   1. "Buy now" which will lead to the next channel
   2. "Back to form", which returns to the (restored) form
2. The Cart Processor Channel processes the Payment itself (Off-Page Payment for example). Every default channel comes with two "virtual funnel actions":
   1. "On Success": Which will lead to the next channel
   2. "On Error:" "Back to form", which returns to the (restored) form
3. The Email Channel (Which should be familiar to you) triggers an email submission. Since it's also a default chanel, the two virtual funnel actions most be defined:
   1. "On Success": Which will lead to the next channel
   2. "On Error:" "Back to form", which returns to the (restored) form
4. The last Funnel Layer renders a "Thank You" Page (_"Dynamic Layout Layer"_) by using a custom snippet. There is just one action: 
   1. "Done": A disabled action, so no button will be rendered

***

## Some important Facts
- An existing workflow cannot be transformed into a funnel, just on creation time
- A Funnel is not able to process the default success management, use a final funnel layer to restore this feature
- The initial form will be stored within a storage provider. By default, a session storage will be shipped, but you're able to create your own storage provider
- Every funnel layer creates a physical URL (Initially a UUID/v4 will be generated, but you're able to rename them)
- Every funnel layer receives the `SubmissionEvent` object, so you're able to process users data which has been collected via form builders root form
- Every funnel layer will be submitted as a form. If a funnel layer provides some additional form data, it will be stored within the storage provider via `FunnelRuntimeData`
- After the last channel has been called OR an exception raised, a `funnel_finished` flag will be added to the url. This indicates, that the workflow is done and the current storage will be flushed

***

## Enable Funnel Feature
Funnels are disabled by default, so let's enable it:

```yaml
form_builder:
    funnel:
        enabled: true
```

## Add Routes
Import preconfigured funnel routes from the FormBuilder package or copy them into your project.
Don't freak out, we're talking about one tiny route only:

```yaml
# config/routes.yaml
form_builder_routing_funnels:
    resource: '@FormBuilderBundle/Resources/config/pimcore/routing_funnels.yml'

# or import routes without the {locale} flag in routes
# form_builder_routing_funnels:
#     resource: '@FormBuilderBundle/Resources/config/pimcore/routing_funnels_not_localized.yml'
```

## Further Information
- [Create a Funnel](./Funnel/0_CreateFunnel.md)
- [Funnel Layer & Custom Funnel Layers](./Funnel/10_FunnelLayer.md)
- [Funnel Actions & Custom Funnel Actions](./Funnel/20_FunnelActions.md)
- [Storage Provider & Custom Storage Provider](./Funnel/30_StorageProvider.md)
- [Custom Storage Class](./Funnel/40_CustomStorageClass.md)
- [Root Form Serialization Groups](./Funnel/50_RootForm.md)
