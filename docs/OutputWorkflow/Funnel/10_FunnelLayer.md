## Funnel Layer
A Funnel Layer is basically the same as an output workflow you already know.
The big difference: It's a real frontend page with different actions, triggered by the user itself.

![image](https://user-images.githubusercontent.com/700119/207115270-37c44d6e-c493-45b4-ab0f-9f7c3d97e7b6.png)

### Dynamic Layout Layer
By default, there is only one layer, a so-called "_Dynamic Layout Layer_". 

This layer is configured as `dynamicFunnelActionAware`, which means:
- that you need to define you're own actions (See next chapter)
- that you need to place them in your snippet template

So, go ahead, create a snippet and drag it into the "Layout" field.

Next, we need to configure some [funnel actions](./20_FunnelActions.md).

***

### Custom Funnel Layer
It is very easy to configure a custom funnel layer.

#### Configuration
```yaml
services:
    App\Funnel\DummyLayer:
        tags:
            - { name: form_builder.output_workflow.funnel_layer, type: dummyLayer }
```

#### PHP Service
```php
<?php

namespace App\Funnel;

use FormBuilderBundle\Model\FunnelActionDefinition;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerData;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DummyLayer implements FunnelLayerInterface
{
    public function getName(): string
    {
        return 'Dummy Layer';
    }

    public function getFormType(): array
    {
        /**
        * If you want to configure additional fields in backend, add them here
        */
        
        return [
            'type'    => TextType::class,
            'options' => []
        ];
    }

    public function dynamicFunnelActionAware(): bool
    {
        /**
        * If you're returning true,
        * user is allowed to define its own funnel actions, 
        * and you should return an empty array in the method below: getFunnelActionDefinitions()   
        */
       
        return false;
    }

    public function getFunnelActionDefinitions(): array
    {
        /**
        * If you want to define some predefined actions,
        * dynamicFunnelActionAware() needs to return false.
        * Then you're able to add them in your funnel layer layout (see template below) 
        */
        
        return [
            new FunnelActionDefinition('dummyButton', 'Top Button')
        ];
    }


    public function buildForm(FunnelLayerData $funnelLayerData, FormBuilderInterface $formBuilder): void
    {
        /**
        * If you need additional information from a given user,
        * just add some additional fields to the Layer form type.
        * You're also able to fetch this submitted data in upcoming channels 
        * via SubmissionEvent->getFunnelRuntimeData()   
        */
        
        $formBuilder->add('consent', CheckboxType::class, ['constraints' => [new IsTrue()]]);
    }

    public function handleFormData(FunnelLayerData $funnelLayerData, array $formData): array
    {
        return $formData;
    }

    public function buildView(FunnelLayerData $funnelLayerData): void
    {
        $funnelLayerConfiguration = $funnelLayerData->getFunnelLayerConfiguration();

        $viewArguments = [
            'dummyField' => $funnelLayerConfiguration['dummyField']
        ];

        $funnelLayerData->setFunnelLayerView('/funnel/dummy_layer.html.twig');
        $funnelLayerData->setFunnelLayerViewArguments($viewArguments);
    }
}
```

#### JS Service
```javascript
pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.dummyLayer');
Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.dummyLayer = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.funnelLayer.abstractLayer, {

    getConfigItems: function () {
        return [
            {
                xtype:'textfield',
                name: 'dummyField',
                fieldLabel: 'My Dummy Field'
            }
        ];
    }
});
```

#### Twig Partial
```twig
{% if formThemePath is not null %}
    {% form_theme form formThemePath %}
{% endif %}

{% block funnel_content %}

    {{ dump(dummyField) }}
    
    <div class="row">
        {{ form_widget(form.consent) }}
    </div>
    
    {% if funnelActions.hasByName('dummyButton') %}
        {{ form_widget(form.dummyButton, {attr: {class: 'btn-primary'}, label: 'Do something' }) }}
    {% endif %}

{% endblock funnel_content %}
```