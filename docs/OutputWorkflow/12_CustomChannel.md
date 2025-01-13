# Custom Output Workflow Channel
To create a custom output workflow channel, you need to create some configuration classes. Let's do this!

## Service Definition

```yaml
services:
    App\FormBuilder\MyChannel:
        tags:
            - { name: form_builder.output_workflow.channel, type: myChannel }
```

## Output Transformer
Read [here](./15_OutputTransformer.md#custom-output-transformer) how to add a single output transformer to your new custom channel.

## Channel Context
Read [here](./13_ChannelContext.md) how to support channel context within your new custom channel.

## PHP Configuration Form Type Class
```php
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('myConfigField', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

```
## PHP Service Class

```php
<?php

namespace App\FormBuilder;

use App\Form\MyChannelType;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;

class MyChannel implements ChannelInterface
{
    public function __construct(protected FormValuesOutputApplierInterface $formValuesOutputApplier)
    {
    }

    public function getFormType(): string
    {
        // you need to create a form type for backend configuration validation.
        return MyChannelType::class;
    }

    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    public function getUsedFormFieldNames(array $channelConfiguration): array
    {
        return [];
    }

    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {
        $locale = $submissionEvent->getLocale() ?? $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();
        
        // Output Transformer (See section "Output Transformer" above).
        // This is optional, if you don't want to use any output transformer, you could use the raw form values directly.
        $formData = $this->formValuesOutputApplier->applyForChannel($form, [], 'myChannel', $locale);

        // now, do your work...
    }
}
```

## ExtJS Class
You need to register this class via `\Pimcore\Event\BundleManagerEvents::JS_PATHS` Event. 
Make sure, that you've defined a low priority, to allow loading fb resources first!.

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