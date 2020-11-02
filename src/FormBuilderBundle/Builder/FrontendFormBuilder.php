<?php

namespace FormBuilderBundle\Builder;

use FormBuilderBundle\EventSubscriber\FormBuilderSubscriber;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Factory\FormDataFactoryInterface;
use FormBuilderBundle\Form\RuntimeData\FormRuntimeDataAllocatorInterface;
use FormBuilderBundle\Form\Type\DynamicFormType;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendFormBuilder
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
     * @var FormRuntimeDataAllocatorInterface
     */
    protected $formRuntimeDataAllocator;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormDataFactoryInterface
     */
    protected $formDataFactory;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @param FormBuilderSubscriber             $formBuilderSubscriber
     * @param Configuration                     $configuration
     * @param RequestStack                      $requestStack
     * @param FormRuntimeDataAllocatorInterface $formRuntimeDataAllocator
     * @param FormFactoryInterface              $formFactory
     * @param FormDataFactoryInterface          $formDataFactory
     * @param UrlGeneratorInterface             $router
     */
    public function __construct(
        FormBuilderSubscriber $formBuilderSubscriber,
        Configuration $configuration,
        RequestStack $requestStack,
        FormRuntimeDataAllocatorInterface $formRuntimeDataAllocator,
        FormFactoryInterface $formFactory,
        FormDataFactoryInterface $formDataFactory,
        UrlGeneratorInterface $router
    ) {
        $this->formBuilderSubscriber = $formBuilderSubscriber;
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
        $this->formRuntimeDataAllocator = $formRuntimeDataAllocator;
        $this->formFactory = $formFactory;
        $this->formDataFactory = $formDataFactory;
        $this->router = $router;
    }

    /**
     * @param FormDefinitionInterface $formDefinition
     * @param array                   $formRuntimeData
     *
     * @return FormInterface
     *
     * @throws \Exception
     */
    public function buildForm(FormDefinitionInterface $formDefinition, $formRuntimeData = [])
    {
        $defaults = [
            'form_preset'   => null,
            'form_template' => null
        ];

        if (is_array($formRuntimeData)) {
            $formRuntimeData = array_merge($defaults, $formRuntimeData);
        }

        $request = $this->requestStack->getCurrentRequest();
        $formDefinitionConfig = $formDefinition->getConfig();

        $formAttributes = [];

        if ($formDefinitionConfig['noValidate'] === false) {
            $formAttributes['novalidate'] = 'novalidate';
        }

        $formAttributes['class'] = 'formbuilder';
        $formAttributes['data-template'] = $formRuntimeData['form_template'];

        if ($formDefinitionConfig['useAjax'] === true) {
            $formAttributes['data-ajax-structure-url'] = $this->router->generate('form_builder.controller.ajax.url_structure');
            $formAttributes['class'] = $formAttributes['class'] . ' ajax-form';
        }

        //@todo: implement inline functionality.
        //$formAttributes['class'] = 'form-inline';

        if (isset($formDefinitionConfig['attributes']) && is_array($formDefinitionConfig['attributes'])) {
            $formAttributes = $this->addFormAttributes($formAttributes, $formDefinitionConfig['attributes']);
        }

        $builder = $this->formFactory->createNamedBuilder(
            sprintf('formbuilder_%s', $formDefinition->getId()),
            DynamicFormType::class,
            $this->formDataFactory->createFormData($formDefinition),
            [
                'method'            => $formDefinitionConfig['method'],
                'action'            => $formDefinitionConfig['action'] === '/' ? $request->getUri() : $formDefinitionConfig['action'],
                'current_form_id'   => $formDefinition->getId(),
                'conditional_logic' => $formDefinition->getConditionalLogic(),
                'runtime_data'      => $formRuntimeData,
                'attr'              => $formAttributes,
            ]
        );

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
                    $currentAttributes[$attribute['option']] = array_merge($currentAttributes[$attribute['option']], (array) $attribute['value']);
                } else {
                    $currentAttributes[$attribute['option']] .= ' ' . (string) $attribute['value'];
                }
            } else {
                $currentAttributes[$attribute['option']] = (string) $attribute['value'];
            }
        }

        return $currentAttributes;
    }
}
