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

            if (strpos($key, 'formbuilder_') === false) {
                continue;
            }

            if (isset($parameters['formId'])) {
                return $parameters['formId'];
            }
        }

        return null;
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
            'form_preset'   => null,
            'form_template' => null
        ];

        $formOptions = array_merge($defaults, $userOptions);

        $request = $this->requestStack->getCurrentRequest();
        $formEntity = $this->formManager->getById($id);
        $formConfig = $formEntity->getConfig();

        $formAttributes = [];
        if ($formConfig['noValidate'] === false) {
            $formAttributes['novalidate'] = 'novalidate';
        }

        $formAttributes['class'] = 'formbuilder';
        $formAttributes['data-template'] = $formOptions['form_template'];

        if ($formConfig['useAjax'] === true) {
            $formAttributes['data-ajax-structure-url'] = $this->router->generate('form_builder.controller.ajax.url_structure');
            $formAttributes['class'] = $formAttributes['class'] . ' ajax-form';
        }

        //@todo: implement inline functionality.
        //$formAttributes['class'] = 'form-inline';

        if (isset($formConfig['attributes']) && is_array($formConfig['attributes'])) {
            $formAttributes = $this->addFormAttributes($formAttributes, $formConfig['attributes']);
        }

        $builder = $this->formFactory->createNamedBuilder(
            'formbuilder_' . $formEntity->getId(),
            DynamicFormType::class,
            $formEntity,
            [
                'method'            => $formConfig['method'],
                'action'            => $formConfig['action'] === '/' ? $request->getUri() : $formConfig['action'],
                'current_form_id'   => $formEntity->getId(),
                'conditional_logic' => $formEntity->getConditionalLogic(),
                'attr'              => $formAttributes,
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

    /**
     * @param array $currentAttributes
     * @param array $attributes
     *
     * @return array
     */
    private function addFormAttributes(array $currentAttributes, array $attributes)
    {
        foreach ($attributes as $key => $attribute) {
            // legacy
            if (!isset($attribute['option']) || is_null($attribute['option'])) {
                continue;
            }

            if (isset($currentAttributes[$attribute['option']])) {
                if (is_array($currentAttributes[$attribute['option']])) {
                    $currentAttributes[$attribute['option']] = array_merge($currentAttributes[$attribute['option']], (array)$attribute['value']);
                } else {
                    $currentAttributes[$attribute['option']] .= ' ' . (string)$attribute['value'];
                }
            } else {
                $currentAttributes[$attribute['option']] = (string)$attribute['value'];
            }
        }

        return $currentAttributes;
    }
}
