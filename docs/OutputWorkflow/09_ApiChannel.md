# API Channel
![image](https://user-images.githubusercontent.com/700119/145599712-37b8468e-975e-4f3e-9fd5-a82dd76e3c53.png)

Use the mail channel to submit structured to any kind of API you want.

- [Mapping](./09_ApiChannel.md#mapping) | Map form fields to predefined api fields
- [API Provider](./09_ApiChannel.md#api-provider) | Create a custom api provider
- [Guard Event Listener](./09_ApiChannel.md#guard-event-listener) | Hook into api provider process
- [Code Example: Trigger Value](./09_ApiChannel.md#example-trigger-value) | Dispatch Channel by given trigger
- [Field Transformer](./16_FieldTransformer.md) *(Optional)* | Use a field transformer to modify single api field values

## Note
The FormBuilder API Channel does **not** ship preconfigured API Provider. They can be simple but also complex. But no worries,
it's quite easy to integrate your own api provider, read more about it [here](./09_ApiChannel.md#api-provider)

## Available Options

| Name         | Type                   | Description                                         |
|--------------|------------------------|-----------------------------------------------------|
| Api Provider | `ApiProviderInterface` | Select your API Provider.                           |
| Options      | `mixed`                | If available, various provider configuration fields |

## Mapping
![image](https://user-images.githubusercontent.com/700119/145618709-686d5022-1ed9-4722-9600-0d41eccf55a3.png)

If the API Provider supports predefined API fields, you're able to map form fields to these fields which will show up in a
dropdown element. If there are no predefined API fields, you need to enter them manually.

### Container "Fieldset" Mapping
If an API field is assigned to the fieldset itself, the child elements will be created as an array branch. Otherwise, child
elements will be assigned flat.

### Container "Repeater" Mapping
If no API field is assigned to the repeater itself, the child elements will be skipped.

***

## API Provider
Integrating an api provider is very simple. In this example, we're going to set up an API provider for MailChimp.

### API Configuration Fields
Every API Provider is allowed to provide custom configuration fields (see example below). This allows you to select various data
for each form (like a campaign ID in MailChimp)

### API Predefined Fields
If the API Provider returns predefined you **must** map your form fields with these given fields.

> Note! You're allowed to add a predefined only once, but you're allowed to add multiple predefined properties to a single form field!

```bash
$ composer require mailchimp/marketing
```

First, we need to register a new service:
```yml
AppBundle\FormBuilder\ApiProvider\MailChimpApiProvider:
    autowire: true
    public: false
    tags:
        - { name: form_builder.api.provider, identifier: mailchimp }
```

Then, we're going to implement the service itself:
```php
<?php

namespace AppBundle\FormBuilder\ApiProvider;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Api\ApiData;
use FormBuilderBundle\OutputWorkflow\Channel\Api\ApiProviderInterface;

class MailChimpApiProvider implements ApiProviderInterface
{
    public function getName(): string
    {
        return 'MailChimp';
    }
    
    public function getProviderConfigurationFields(FormDefinitionInterface $formDefinition): array
    {
        $mailchimp = $this->getClient();
        $campaignsData = $mailchimp->campaigns->list();

        $campaignStore = [];
        foreach ($campaignsData->campaigns as $campaign) {
            $campaignStore[] = [
                'value' => $campaign->id,
                'label' => $campaign->settings->title
            ];
        }

        return [
            [
                'type'     => 'text',
                'label'    => 'My Config',
                'name'     => 'myConfig',
                'required' => true,
            ],
            [
                'type'     => 'select',
                'label'    => 'Campaign',
                'name'     => 'campaign',
                'store'    => $campaignStore,
                'required' => true,
            ]
        ];
    }

    public function getPredefinedApiFields(FormDefinitionInterface $formDefinition, array $providerConfiguration): array
    {
        // maybe they will come from a remote campaign list.
        // just return an empty array if you don't want to provide predefined api fields.
        
        $fields = [
            'EMAIL',
            'MMERGE6',
            'FNAME',
            'LNAME'
        ];
        
        if ($providerConfiguration['campaign'] === '123') {
            $fields[] = 'SPECIAL_FIELD';
        }
        
        return $fields;
    }
    
    public function process(ApiData $apiData): void
    {
        $mailchimp = $this->getClient();
        $campaignId = $apiData->getProviderConfigurationNode('campaign');

        $campaigns = $mailchimp->campaigns->get($campaignId);

        $body = [
            'status'        => 'subscribed',
            'email_address' => $apiData->getApiNode('EMAIL'),
            'merge_fields'  => $apiData->getApiNodes()
        ];

        try {
            $mailchimp->lists->addListMember($campaignId, $body);
        } catch(\Throwable $e) {

            // don't forget to wrap your remote calls in a correct exception:
            
            // I. fail silently. no error will be visible to the user
           
            // II. OR: only channel should get bypassed (upcoming channels will be processed)
            // error will be visible to the user after workflow has been completely dispatched
            throw new GuardChannelException($e->getMessage());
                
            // III. OR: workflow should not go one. cancel from now on
            // error will be visible to the user after workflow has been completely dispatched
            throw new GuardOutputWorkflowException($e->getMessage());
        }
    }
    
    protected function getClient(): MailchimpMarketing\ApiClient 
    {
        $mailchimp = new MailchimpMarketing\ApiClient();

        $mailchimp->setConfig([
            'apiKey' => 'YOUR_API_KEY',
            'server' => 'YOUR_SERVER_PREFIX'
        ]);

        return $mailchimp;
    }
}
```

***

## Guard Event Listener
As in every output channel, you're able to hook into the dispatch event via `OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH` event.

```php
<?php

namespace AppBundle\FormBuilder;

use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use FormBuilderBundle\OutputWorkflow\Channel\Api\ApiData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\FormBuilder\ApiProvider\MailChimpApiProvider;

class OutputWorkflowEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH  => 'checkSubject',
        ];
    }

    public function checkSubject(ChannelSubjectGuardEvent $event): void
    {
        $subject = $event->getSubject();

        // only apply if subject represents an ApiData instance
        if (!$subject instanceof ApiData) {
            return;
        }
        
        // only apply for specific api provider
        if( $subject->getApiProviderName() !== 'mailchimp') {
            return;
        }
        
        // different fail scenarios can be applied:
        
        $event->shouldFail('My invalid message for a specific channel! Allow further channels to pass!', true);
        // OR
        $event->shouldFail('My invalid message! If this happens, no further channel will be executed!', false);
    
        // silently skip channel
        if ($subject->getProviderConfigurationNode('myConfig') === 'a special value') {
            $event->shouldSuspend();
            
            return;
        }
    }
}
```

**

## Example: Trigger Value
In some scenarios, you want to trigger your API channel only, if a specific form value is given (A checkbox for example). We'll
release this in a [dedicated feature](https://github.com/dachcom-digital/pimcore-formbuilder/issues/304) in upcoming versions. 
Until then, this can be solved by use a custom provider configuration field:

### Configuration Field
First, we have to add a configuration field. Let's call it `consentTriggerValue`:
```php
// AppBundle\FormBuilder\ApiProvider\MailChimpApiProvider
public function getProviderConfigurationFields(FormDefinitionInterface $formDefinition): array
{
    return [
        [
            'type'     => 'text',
            'label'    => 'Consent Trigger Value',
            'name'     => 'consentTriggerValue',
            'required' => false,
        ],
        ...
    ];
}
```

### Trigger Field
Then, we need to add a trigger field. Let's call it `CONSENT_TRIGGER`:
```php
// AppBundle\FormBuilder\ApiProvider\MailChimpApiProvider
public function getPredefinedApiFields(FormDefinitionInterface $formDefinition, array $providerConfiguration): array
{
    return [
        ...
        'CONSENT_TRIGGER'
    ];
}
```

### Channel Configuration
Append our freshly created fields in the API output channel:
![image](https://user-images.githubusercontent.com/700119/145799355-cc47cf5f-d5e5-464c-808c-1fc3abe30bec.png)
![image](https://user-images.githubusercontent.com/700119/145799502-5a54d0e7-a7e3-401c-8a9f-bc20966894b6.png)

### Dispatch Process
And finally, check given consent value in your `process` method:
```php
public function process(ApiData $apiData): void
{
    $consentTriggerValue = $apiData->getProviderConfigurationNode('consentTriggerValue');

    // configuration values are always available as strings
    if ($consentTriggerValue === 'true') {
        $consentTriggerValue = true;
    }

    // in our example we're dealing with a checkbox.
    // which means that it's not available as node if unchecked
    if (!$apiData->hasApiNode('CONSENT_TRIGGER')) {
        return;
    }

    // otherwise, it has to be the same value as defined in our configuration node!
    if ($consentTriggerValue !== $apiData->getApiNode('CONSENT_TRIGGER')) {
        return;
    }

    ...
}
```

And you're done. This API channel will only dispatch if a user gives consent to it.