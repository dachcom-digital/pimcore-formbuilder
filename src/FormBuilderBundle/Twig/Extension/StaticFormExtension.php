<?php

namespace FormBuilderBundle\Twig\Extension;

use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Assembler\FormAssembler;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StaticFormExtension extends AbstractExtension
{
    protected FormAssembler $formAssembler;

    public function __construct(FormAssembler $formAssembler)
    {
        $this->formAssembler = $formAssembler;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'form_builder_static',
                [$this, 'generateForm'],
                ['needs_environment' => true, 'needs_context' => true, 'is_safe' => ['html']]
            )
        ];
    }

    public function generateForm(Environment $environment, array $context, array $formOptions = []): string
    {
        $defaultOptions = [
            'form_id'            => null,
            'form_template'      => null,
            'preset'             => null,
            'output_workflow'    => null,
            'custom_options'     => [],
            // deprecated options since v3.3 below
            'send_copy'          => false,
            'mail_template'      => null,
            'copy_mail_template' => null,
        ];

        $options = array_merge($defaultOptions, $formOptions);

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setSendCopy($options['send_copy']);
        $optionBuilder->setMailTemplate($options['mail_template']);
        $optionBuilder->setCopyMailTemplate($options['copy_mail_template']);
        $optionBuilder->setFormPreset($options['preset']);
        $optionBuilder->setCustomOptions($options['custom_options']);
        $optionBuilder->setOutputWorkflow($options['output_workflow']);

        $viewVars = array_merge(
            ['editmode' => $context['editmode']],
            $this->formAssembler->assembleViewVars($optionBuilder)
        );

        return $environment->render('@FormBuilder/Form/form.html.twig', $viewVars);
    }
}
