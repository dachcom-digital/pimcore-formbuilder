<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Assembler\FormAssembler;

class DefaultController extends FrontendController
{
    public function defaultAction(Request $request)
    {
    }

    public function snippetAction(Request $request)
    {
    }

    public function twigRenderAction(Request $request)
    {
    }

    public function javascriptAction(Request $request)
    {
    }

    public function dynamicMultiFileAction(Request $request)
    {
        $options = [];

        if ($this->document->getKey() === 'drop-zone') {
            $options = ['libPath' => 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js'];
        } elseif ($this->document->getKey() === 'fine-uploader') {
            $options = ['libPath' => 'https://cdnjs.cloudflare.com/ajax/libs/file-uploader/5.16.2/jquery.fine-uploader/jquery.fine-uploader.min.js'];
        }

        return $this->renderTemplate('Default/dynamic-multi-file.html.twig', ['options' => $options]);
    }

    public function controllerRenderAction(Request $request)
    {
        $options = [
            'form_id'            => $this->document->getProperty('form_id'),
            'form_template'      => 'bootstrap_4_layout.html.twig',
            'main_layout'        => false,
            'send_copy'          => true,
            'mail_template'      => $this->document->getProperty('mail_id'),
            'copy_mail_template' => $this->document->getProperty('mail_copy_id'),
            'preset'             => null
        ];

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($options['form_id']);
        $optionBuilder->setMainLayout($options['main_layout']);
        $optionBuilder->setFormTemplate($options['form_template']);
        $optionBuilder->setSendCopy($options['send_copy']);
        $optionBuilder->setMailTemplate($options['mail_template']);
        $optionBuilder->setCopyMailTemplate($options['copy_mail_template']);
        $optionBuilder->setFormPreset($options['preset']);

        /** @var FormAssembler $assembler */
        $assembler = $this->container->get(FormAssembler::class);

        return $this->renderTemplate(
            '@FormBuilder/Form/form.html.twig',
            $assembler->assembleViewVars($optionBuilder)
        );
    }
}
