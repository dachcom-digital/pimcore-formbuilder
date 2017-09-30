<?php

namespace FormBuilderBundle\Twig\Extension;

use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Assembler\FormAssembler;

class StaticFormExtension extends \Twig_Extension
{
    /**
     * @var FormAssembler
     */
    protected $formAssembler;

    /**
     * LayoutExtension constructor.
     *
     * @param FormAssembler $formAssembler
     */
    public function __construct(FormAssembler $formAssembler)
    {
        $this->formAssembler = $formAssembler;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('form_builder_static', [$this, 'generateForm'],
                ['needs_environment' => TRUE, 'needs_context' => TRUE, 'is_safe' => ['html']]
            )
        ];
    }

    public function generateForm(\Twig_Environment $environment, $context, $formOptions = [])
    {
        $defaultOptions = [
            'form_id'             => NULL,
            'form_template'       => NULL,
            'send_copy'           => FALSE,
            'mail_template'       => NULL,
            'copy_mail_template'  => NULL,
            'preset'              => NULL
        ];

        $options = array_merge($defaultOptions, $formOptions);

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setSendCopy($options['send_copy']);
        $optionBuilder->setMailTemplate($options['mail_template']);
        $optionBuilder->setCopyMailTemplate($options['copy_mail_template']);
        $optionBuilder->setFormPreset($options['preset']);

        $this->formAssembler->setFormOptionsResolver($optionBuilder);

        $viewVars = array_merge(
            ['editmode' => $context['editmode']],
            $this->formAssembler->assembleViewVars()
        );

        return $environment->render('@FormBuilder/Form/form.html.twig', $viewVars);
    }
}