<?php

namespace FormBuilderBundle\Builder;

use FormBuilderBundle\EventSubscriber\FormBuilderSubscriber;
use FormBuilderBundle\Factory\FormDataFactoryInterface;
use FormBuilderBundle\Form\Type\DoubleOptInType;
use FormBuilderBundle\Form\Type\DynamicFormType;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FrontendFormBuilder
{
    public function __construct(
        protected FormBuilderSubscriber $formBuilderSubscriber,
        protected RequestStack $requestStack,
        protected FormFactoryInterface $formFactory,
        protected FormDataFactoryInterface $formDataFactory,
        protected UrlGeneratorInterface $router
    ) {
    }

    public function buildDoubleOptInForm(
        FormDefinitionInterface $formDefinition,
        array $formAttributes = [],
        bool $isHeadlessForm = false,
        bool $useCsrfProtection = true
    ): FormInterface {

        $formDefinitionConfig = $formDefinition->getConfiguration();
        $doubleOptInConfig = $formDefinition->getDoubleOptInConfig();

        $request = !$isHeadlessForm && $this->requestStack->getMainRequest() instanceof Request ? $this->requestStack->getMainRequest() : null;

        if ($formDefinitionConfig['noValidate'] === false) {
            $formAttributes['novalidate'] = 'novalidate';
        }

        $formAttributes['class'] = 'formbuilder formbuilder-double-opt-in';

        if ($formDefinitionConfig['useAjax'] === true && $isHeadlessForm === false) {
            $formAttributes['data-ajax-structure-url'] = $this->router->generate('form_builder.controller.ajax.url_structure');
            $formAttributes['class'] = sprintf('%s ajax-form', $formAttributes['class']);
        }

        $action = $formDefinitionConfig['action'];
        if (!$isHeadlessForm && $request instanceof Request) {
            $action = $formDefinitionConfig['action'] === '/' ? $request->getUri() : $formDefinitionConfig['action'];
        }

        $builder = $this->formFactory->createNamedBuilder(
            $isHeadlessForm === true ? '' : sprintf('formbuilder_double_opt_in_%s', $formDefinition->getId()),
            DoubleOptInType::class,
            null,
            [
                'action'                         => $action,
                'method'                         => $formDefinitionConfig['method'],
                'attr'                           => $formAttributes,
                'csrf_protection'                => $useCsrfProtection,
                'render_conditional_logic_field' => !$isHeadlessForm,
                'render_form_id_field'           => !$isHeadlessForm,
                'current_form_id'                => $formDefinition->getId(),
                'is_headless_form'               => $isHeadlessForm,
                'double_opt_in_instruction_note' => $doubleOptInConfig['instructionNote'] ?? null
            ]
        );

        $form = $builder->getForm();

        if (!$isHeadlessForm && $request instanceof Request) {
            $form->handleRequest($request);
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function buildForm(
        FormDefinitionInterface $formDefinition,
        array $formRuntimeData = [],
        array $formAttributes = [],
        array $formData = [],
        bool $useCsrfProtection = true
    ): FormInterface {

        $defaults = [
            'form_preset'   => null,
            'form_template' => null
        ];

        if (is_array($formRuntimeData)) {
            $formRuntimeData = array_merge($defaults, $formRuntimeData);
        }

        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $formDefinitionConfig = $formDefinition->getConfiguration();

        if ($formDefinitionConfig['noValidate'] === false) {
            $formAttributes['novalidate'] = 'novalidate';
        }

        $formAttributes['class'] = 'formbuilder';
        $formAttributes['data-template'] = $formRuntimeData['form_template'];

        if ($formDefinitionConfig['useAjax'] === true) {
            $formAttributes['data-ajax-structure-url'] = $this->router->generate('form_builder.controller.ajax.url_structure');
            $formAttributes['class'] = sprintf('%s ajax-form', $formAttributes['class']);
        }

        //@todo: implement inline functionality.
        //$formAttributes['class'] = 'form-inline';

        $formOptions = [
            'csrf_protection' => $useCsrfProtection,
            'action'          => $formDefinitionConfig['action'] === '/' ? $request->getUri() : $formDefinitionConfig['action'],
        ];

        $builder = $this->getBuilder($formDefinition, $formRuntimeData, $formAttributes, $formData, $formOptions);

        $form = $builder->getForm();
        $form->handleRequest($request);

        return $form;
    }

    public function buildHeadlessForm(
        FormDefinitionInterface $formDefinition,
        array $formRuntimeData = [],
        array $formAttributes = [],
        array $formData = [],
        bool $useCsrfProtection = true
    ): FormInterface {

        $formDefinitionConfig = $formDefinition->getConfiguration();

        $formOptions = [
            'csrf_protection'                => $useCsrfProtection,
            'action'                         => $formDefinitionConfig['action'],
            'render_conditional_logic_field' => false,
            'render_form_id_field'           => false,
        ];

        $builder = $this->getBuilder($formDefinition, $formRuntimeData, $formAttributes, $formData, $formOptions, true);

        return $builder->getForm();
    }

    private function getBuilder(
        FormDefinitionInterface $formDefinition,
        array $formRuntimeData,
        array $formAttributes,
        array $formData = [],
        array $formOptions = [],
        bool $isHeadlessForm = false
    ): FormBuilderInterface {

        $formDefinitionConfig = $formDefinition->getConfiguration();

        if (isset($formDefinitionConfig['attributes']) && is_array($formDefinitionConfig['attributes'])) {
            $formAttributes = $this->addFormAttributes($formAttributes, $formDefinitionConfig['attributes']);
        }

        $builder = $this->formFactory->createNamedBuilder(
            $isHeadlessForm === true ? '' : sprintf('formbuilder_%s', $formDefinition->getId()),
            DynamicFormType::class,
            $this->formDataFactory->createFormData($formDefinition, $formData),
            array_merge([
                'method'            => $formDefinitionConfig['method'],
                'is_headless_form'  => $isHeadlessForm,
                'current_form_id'   => $formDefinition->getId(),
                'conditional_logic' => $formDefinition->getConditionalLogic(),
                'runtime_data'      => $formRuntimeData,
                'attr'              => $formAttributes,
            ], $formOptions)
        );

        $builder->addEventSubscriber($this->formBuilderSubscriber);

        return $builder;
    }

    private function addFormAttributes(array $currentAttributes, array $attributes): array
    {
        foreach ($attributes as $attribute) {

            // legacy
            if (!isset($attribute['option'])) {
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
