# API Channel

![image](https://user-images.githubusercontent.com/700119/145599712-37b8468e-975e-4f3e-9fd5-a82dd76e3c53.png)

Use the mail channel to submit structured to any kind of API you want.

## Note

**The FormBuilder API Channel does **not** ship preconfigured API Provider. They can be simple but also complex. But no worries,
it's quite easy to integrate your own api provider, read more about it [here](./09_ApiChannel.md#api-provider)

## Available Options

| Name | Type        | Description |
|------|-------------|-------------|
| Api Provider| `ApiProviderInterface` | Select your API Provider. |

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
Every API Provider is allowed to provide custom configuration fields (see example below).
This allows you to select various data for each form (like a campaign ID in MailChimp)

### API Predefined Fields
If the API Provider returns predefined you **must** map your form fields with these given fields.

### Requirements
```bash
$ composer require mailchimp/marketing
```

First, we need to register a new service:

```yml
AppBundle\Formbuilder\ApiProvider\MailChimpApiProvider:
    autowire: true
    public: false
    tags:
        - { name: form_builder.api.provider, identifier: mailchimp }
```

Then, we're going to implement the service itself:

```php
<?php

namespace AppBundle\Formbuilder\ApiProvider;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Api\ApiData;
use FormBuilderBundle\OutputWorkflow\Channel\Api\ApiProviderInterface;

class MailChimpApiProvider implements ApiProviderInterface
{
    public function getName()
    {
        return 'MailChimp';
    }
    
    public function getApiConfigurationFields(FormDefinitionInterface $formDefinition)
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

    public function getPredefinedApiFields(FormDefinitionInterface $formDefinition)
    {
        // maybe they will come from a remote campaign list.
        // just return an empty array if you don't want to provide predefined api fields.
        
        return [
            'EMAIL',
            'FNAME',
            'LNAME',
            'MMERGE6',
        ];
    }
    
    public function process(ApiData $apiData)
    {
        $mailchimp = $this->getClient();
        $campaignId = $apiData->getAPiConfigurationNode('campaign');

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
    
    protected function getClient() 
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
