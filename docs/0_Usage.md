# Usage
There are **three** ways to render your form.
> **Important:** It's possible to use multiple forms per page but **never** render the same form twice on the same page!

## Headless Mode
If you want to use FormBuilder in headless mode, please check out the documentation [here](./1_HeadlessMode.md).

*** 

## Usage I. Area Brick
This is the most used one. Just place a form element (Area Brick) somewhere on your document. 
Configure it via the available edit button.

### Options for Usage II. and III.
Before we start, check out the available options. 
Those are (only) needed for the twig and controller rendering type.

| Name              | Description                                                                                                                                                                                       |
|-------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `form_id`         | Can you guess it? It's the Form Id, right.                                                                                                                                                        |
| `form_template`   | Form Template, for example: `bootstrap_4_layout.html.twig`                                                                                                                                        |
| `main_layout`     | This option is only needed if you render a form via a controller. By default, FormBuilder extends a empty default layout. If you want do extend your custom layout, define it: `layout.html.twig` |
| `preset`          | Optional: set a custom preset                                                                                                                                                                     |
| `custom_options`  | Optional (array): Add some custom options as array here to pass them through the whole submission process (available in SubmissionEvent for example                                               |
| `output_workflow` | Define, which output workflow should get dispatched after a form has been successfully submitted. You could use the ID or Name of a output workflow                                               |

## Usage II. Twig
Create a Form using the Twig Extension.

```twig
<div class="form-wrapper">

    <h2>Static Form via Twig</h2>

    {% set config = {
        'form_id': 3,
        'form_template': 'bootstrap_4_layout.html.twig',
        'output_workflow': 'my_output_workflow'
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

namespace App\Controller;

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
            'main_layout'         => 'layout_form.html.twig', // app/Resources/views/layout.html.twig
            'output_workflow'     => 'my_workflow', // or ID of output workflow
            'preset'              => null,
            'custom_options'      => ['foo' => 'bar']
        ];

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setMainLayout($options['main_layout']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setOutputWorkflow($options['output_workflow']);
        $optionBuilder->setFormPreset($options['preset']);
        $optionBuilder->setCustomOptions($options['custom_options']);

        /** @var FormAssembler $assembler */
        $assembler = $this->container->get(FormAssembler::class);

        return $this->renderTemplate(
            '@FormBuilder/Form/form.html.twig', 
            $assembler->assemble($optionBuilder)
        );
    }
}
```