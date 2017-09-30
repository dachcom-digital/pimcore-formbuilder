<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\EventSubscriber\FormBuilderSubscriber;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Type\DynamicFormType;
use FormBuilderBundle\Manager\FormManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds a dynamic form.
 */
class Builder
{
    /**
     * @var FormBuilderSubscriber
     */
    protected $formBuilderSubscriber;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var FormManager
     */
    protected $formManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * Builder constructor.
     *
     * @param FormBuilderSubscriber $formBuilderSubscriber
     * @param Configuration         $configuration
     * @param RequestStack          $requestStack
     * @param FormManager           $formManager
     * @param FormFactoryInterface  $formFactory
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        FormBuilderSubscriber $formBuilderSubscriber,
        Configuration $configuration,
        RequestStack $requestStack,
        FormManager $formManager,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $router
    ) {
        $this->formBuilderSubscriber = $formBuilderSubscriber;
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
        $this->formManager = $formManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * @param Request $request
     *
     * @return null|integer
     */
    public function detectedFormIdByRequest(Request $request)
    {
        foreach ($request->request->all() as $key => $parameters) {

            if (strpos($key, 'formbuilder_') === FALSE) {
                continue;
            }

            if (isset($parameters['formId'])) {
                return $parameters['formId'];
            }
        }

        return NULL;
    }

    /**
     * @param int   $id
     * @param array $userOptions
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function buildForm($id, $userOptions = [])
    {
        $defaults = [
            'form_preset' => NULL
        ];

        $formOptions = array_merge($defaults, $userOptions);

        $request = $this->requestStack->getCurrentRequest();
        $formEntity = $this->formManager->getById($id);
        $formConfig = $formEntity->getConfig();

        $formAttributes = [];
        if ($formConfig['noValidate'] === FALSE) {
            $formAttributes['novalidate'] = 'novalidate';
        }

        if ($formConfig['useAjax'] === TRUE) {
            $formAttributes['data-ajax-structure-url'] = $this->router->generate('form_builder.controller.ajax.url_structure');
            $formAttributes['class'] = 'formbuilder ajax-form';
        }

        //@todo: implement inline functionality.
        //$formAttributes['class'] = 'form-inline';

        $builder = $this->formFactory->createNamedBuilder(
            'formbuilder_' . $formEntity->getId(),
            DynamicFormType::class,
            $formEntity,
            [
                'method'          => $formConfig['method'],
                'action'          => $formConfig['action'] === '/' ? $request->getUri() : $formConfig['action'],
                'current_form_id' => $formEntity->getId(),
                'attr'            => $formAttributes,
            ]
        );

        //add events subscriber
        $this->formBuilderSubscriber->setFormOptions($formOptions);
        $builder->addEventSubscriber($this->formBuilderSubscriber);

        // get final form
        $form = $builder->getForm();

        // Handle request
        $form->handleRequest($request);

        return $form;
    }
}
