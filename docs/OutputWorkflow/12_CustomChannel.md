# Custom Output Workflow Channel
To create a custom output workflow channel, you need to create some configuration classes. Let's do this!

## Service Definition

```yaml

services:
    AppBundle\FormBuilder\MyChannel:
        tags:
            - { name: form_builder.output_workflow.channel, type: myChannel }

```
## Output Transformer
Read [here](./15_OutputTransformer.md#custom-output-transformer) how to add a single output transformer to your new custom channel.

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
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;

class MyChannel implements ChannelInterface
{
    /**
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    public function __construct(FormValuesOutputApplierInterface $formValuesOutputApplier)
    {
        $this->formValuesOutputApplier = $formValuesOutputApplier;
    }

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
        $formConfiguration = $submissionEvent->getFormConfiguration();
        $locale = $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();
        
        // Output Transformer (See section "Output Transformer" above).
        // This is optional, if you don't want to use any output transformer, you could use the raw form values directly.
        $formData = $this->formValuesOutputApplier->applyForChannel($form, [], 'myChannel', $locale);

        // now, do your work...
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