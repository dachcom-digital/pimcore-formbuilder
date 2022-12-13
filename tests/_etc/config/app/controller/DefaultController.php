<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Assembler\FormAssembler;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends FrontendController
{
    public function defaultAction(Request $request): Response
    {
        return $this->renderTemplate('default/default.html.twig');
    }

    public function snippetAction(Request $request): Response
    {
        return $this->renderTemplate('default/snippet.html.twig', [
            'document' => $this->document,
            'editmode' => $this->editmode,
        ]);
    }

    public function twigRenderAction(Request $request): Response
    {
        return $this->renderTemplate('default/twigRender.html.twig');
    }

    public function javascriptAction(Request $request): Response
    {
        return $this->renderTemplate('default/javascript.html.twig', [
            'document' => $this->document,
            'editmode' => $this->editmode,
        ]);
    }

    public function dynamicMultiFileAction(Request $request): Response
    {
        $options = [];

        if ($this->document->getKey() === 'drop-zone') {
            $options = [
                'defaultHandlerPath' => 'https://rawcdn.githack.com/dachcom-digital/jquery-pimcore-formbuilder/v1.0.0/dist/dynamic-multi-file',
                'libPath'            => 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js'
            ];
        } elseif ($this->document->getKey() === 'fine-uploader') {
            $options = [
                'defaultHandlerPath' => 'https://rawcdn.githack.com/dachcom-digital/jquery-pimcore-formbuilder/v1.0.0/dist/dynamic-multi-file',
                'libPath'            => 'https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.16.2/jquery.fine-uploader/jquery.fine-uploader.min.js'
            ];
        }

        return $this->renderTemplate('default/dynamic-multi-file.html.twig', ['options' => $options]);
    }

    public function controllerRenderAction(Request $request, FormAssembler $assembler): Response
    {
        $options = [
            'form_id'         => $this->document->getProperty('form_id'),
            'output_workflow' => $this->document->getProperty('output_workflow_id'),
            'form_template'   => 'bootstrap_4_layout.html.twig',
            'main_layout'     => false,
            'preset'          => null,
        ];

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setMainLayout($options['main_layout']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setFormPreset($options['preset']);
        $optionBuilder->setOutputWorkflow($options['output_workflow']);

        return $this->renderTemplate(
            '@FormBuilder/form/form.html.twig',
            $assembler->assemble($optionBuilder)
        );
    }
}
