# Usage
There are **three** ways to render your form.
> **Important:** It's possible to use multiple forms per page but **never** render the same form twice on the same page!

## Usage I. Area Brick
This is the most used one. Just place a form element (Area Brick) somewhere on your document. 
Configure it via the available edit button.

### Options for Usage II. and III.
Before we start, check out the available options. 
Those are (only) needed for the twig and controller rendering type.

| Name | Description |
|------|-------------|
| `form_id` | Can you guess it? It's the Form Id, right. |
| `form_template` | Form Template, for example: `bootstrap_4_layout.html.twig` |
| `main_layout` | This option is only needed if you render a form via a controller. By default, FormBuilder extends a empty default layout. If you want do extend your custom layout, define it: `layout.html.twig` |
| `send_copy` | If you want to submit a copy, set this to `true` |
| `mail_template` | The Mail Template or Mail Template Id |
| `copy_mail_template` | The Copy Mail Template or Copy Mail Template Id |
| `preset` | Optional: set a custom preset |
| `custom_options` | Optional (array): Add some custom options as array here to pass them through the whole submission process (available in SubmissionEvent for example |

## Usage II. Twig
Create a Form using the Twig Extension.

```twig
<div class="form-wrapper">

    <h2>Static Form via Twig</h2>

    {% set config = {
        'form_id': 3,
        'form_template': 'bootstrap_4_layout.html.twig',
        'mail_template': 178,
        'copy_mail_template': 179,
        'send_copy': true,
        'custom_options': { foo: bar }
    } %}

    {{ form_builder_static(config) }}

</div>
```

## Usage III. Controller
Create a Form within a Controller. You may have noticed the `main_layout` parameter. 
This value is important to render your form within a given main layout.

```php
<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use FormBuilderBundle\Assembler\FormAssembler;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends FrontendController
{
    public function formAction(Request $request)
    {
        $options = [
            'form_id'             => 3,
            'form_template'       => 'bootstrap_4_layout.html.twig',
            'main_layout'         => 'layout_form.html.twig', //app/Resources/views/layout.html.twig
            'send_copy'           => false,
            'mail_template'       => 178,
            'copy_mail_template'  => null,
            'preset'              => null,
            'custom_options'      => ['foo' => 'bar']
        ];

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setMainLayout($options['main_layout']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setSendCopy($options['send_copy']);
        $optionBuilder->setMailTemplate($options['mail_template']);
        $optionBuilder->setCopyMailTemplate($options['copy_mail_template']);
        $optionBuilder->setFormPreset($options['preset']);
        $optionBuilder->setCustomOptions($options['custom_options']);

        /** @var FormAssembler $assembler */
        $assembler = $this->container->get(FormAssembler::class);
        $assembler->setFormOptionsResolver($optionBuilder);

        return $this->renderTemplate(
            '@FormBuilder/Form/form.html.twig', 
            $assembler->assembleViewVars()
        );
    }
}
```