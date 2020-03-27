# Custom Output Workflow Channel
To create a custom output workflow channel, you need to create some configuration classes. Let's do this!

## Service Definition

```yaml

services:
    AppBundle\FormBuilder\MyChannel:
        tags:
            - { name: form_builder.output_workflow.channel, type: myChannel }

```

## PHP Configuration Form Type Class

```php
<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyChannelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('myConfigField', TextType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}


```
## PHP Service Class

```php
<?php

namespace AppBundle\FormBuilder;

use AppBundle\Form\MyChannelType;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class MyChannel implements ChannelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType(): string
    {
        // you need to create a form type for backend configuration validation.
        return MyChannelType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedFormFieldNames(array $channelConfiguration)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration)
    {
        // do your work.
    }
}
```

## ExtJS Class
You need to register this class in your `AppBundle` via `getJsPaths()`.

```js
pimcore.registerNS('Formbuilder.extjs.formPanel.outputWorkflow.channel.myChannel');
Formbuilder.extjs.formPanel.outputWorkflow.channel.myChannel = Class.create(Formbuilder.extjs.formPanel.outputWorkflow.channel.abstractChannel, {

    panel: null,

    getLayout: function () {

        this.panel = new Ext.form.FormPanel({
            title: false,
            defaults: {
                labelWidth: 200
            },
            items: [{
                xtype: 'textfield',
                value: this.data ? this.data.myConfigField : null,
                fieldLabel: 'My Config Field',
                name: 'myConfigField'
            }]
        });

        return this.panel;
    },

    isValid: function () {
        return this.panel.form.isValid();
    },

    getValues: function () {
       return this.panel.form.getValues();
    }
});

```