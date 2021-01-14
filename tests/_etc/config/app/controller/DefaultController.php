<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Assembler\FormAssembler;

class DefaultController extends FrontendController
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->setViewAutoRender($event->getRequest(), true, 'twig');
    }

    public function defaultAction(Request $request)
    {
    }

    public function snippetAction(Request $request)
    {
    }

    public function javascriptAction(Request $request)
    {
    }

    public function twigRenderAction(Request $request)
    {
    }

    public function controllerRenderAction(Request $request)
    {
        $document = $this->document;

        $options = [
            'form_id'            => $document->getProperty('form_id'),
            'form_template'      => 'bootstrap_4_layout.html.twig',
            'main_layout'        => false,
            'send_copy'          => true,
            'mail_template'      => $document->getProperty('mail_id'),
            'copy_mail_template' => $document->getProperty('mail_copy_id'),
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
