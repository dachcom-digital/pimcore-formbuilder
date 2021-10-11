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
        return $this->renderTemplate('default/snippet.html.twig');
    }

    public function twigRenderAction(Request $request): Response
    {
        return $this->renderTemplate('default/twigRender.html.twig');
    }

    public function javascriptAction(Request $request): Response
    {
        return $this->renderTemplate('default/javascript.html.twig');
    }

    public function dynamicMultiFileAction(Request $request): Response
    {
        $options = [];

        if ($this->document->getKey() === 'drop-zone') {
            $options = [
                'defaultHandlerPath' => 'https://github.com/dachcom-digital/jquery-pimcore-formbuilder/tree/v1.0.0/dist/dynamic-multi-file',
                'libPath'            => 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js'
            ];
        } elseif ($this->document->getKey() === 'fine-uploader') {
            $options = [
                'defaultHandlerPath' => 'https://github.com/dachcom-digital/jquery-pimcore-formbuilder/tree/v1.0.0/dist/dynamic-multi-file',
                'libPath'            => 'https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.16.2/jquery.fine-uploader/jquery.fine-uploader.min.js'
            ];
        }

        return $this->renderTemplate('Default/dynamic-multi-file.html.twig', ['options' => $options]);
    }

    public function controllerRenderAction(Request $request): Response
    {
        $options = [
            'form_id'            => $this->document->getProperty('form_id'),
            'form_template'      => 'bootstrap_4_layout.html.twig',
            'main_layout'        => false,
            'preset'             => null
        ];

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setMainLayout($options['main_layout']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setFormPreset($options['preset']);

        /** @var FormAssembler $assembler */
        $assembler = $this->container->get(FormAssembler::class);

        return $this->renderTemplate(
            '@FormBuilder/Form/form.html.twig',
            $assembler->assembleViewVars($optionBuilder)
        );
    }
}
