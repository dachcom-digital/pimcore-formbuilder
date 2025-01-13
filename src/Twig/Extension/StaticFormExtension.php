<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Twig\Extension;

use FormBuilderBundle\Assembler\FormAssembler;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StaticFormExtension extends AbstractExtension
{
    public function __construct(protected FormAssembler $formAssembler)
    {
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @throws \Exception
     */
    public function generateForm(Environment $environment, array $context, array $formOptions = []): string
    {
        $defaultOptions = [
            'form_id'            => null,
            'form_template'      => null,
            'preset'             => null,
            'output_workflow'    => null,
            'custom_options'     => [],
        ];

        $options = array_merge($defaultOptions, $formOptions);

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setFormPreset($options['preset']);
        $optionBuilder->setCustomOptions($options['custom_options']);
        $optionBuilder->setOutputWorkflow($options['output_workflow']);

        $viewVars = array_merge(
            [
                'editmode' => $context['editmode']
            ],
            $this->formAssembler->assemble($optionBuilder)
        );

        return $environment->render('@FormBuilder/form/form.html.twig', $viewVars);
    }
}
