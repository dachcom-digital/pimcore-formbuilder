<?php

namespace FormBuilderBundle\Builder;

use FormBuilderBundle\EventSubscriber\FormBuilderSubscriber;
use FormBuilderBundle\Factory\FormDataFactoryInterface;
use FormBuilderBundle\Form\Type\DynamicFormType;
use FormBuilderBundle\Model\FormDefinitionInterface;
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

    /**
     * @throws \Exception
     */
    public function buildForm(FormDefinitionInterface $formDefinition, array $formRuntimeData = [], array $formData = []): FormInterface
    {
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

        $formAttributes = [];

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

        if (isset($formDefinitionConfig['attributes']) && is_array($formDefinitionConfig['attributes'])) {
            $formAttributes = $this->addFormAttributes($formAttributes, $formDefinitionConfig['attributes']);
        }

        $builder = $this->formFactory->createNamedBuilder(
            sprintf('formbuilder_%s', $formDefinition->getId()),
            DynamicFormType::class,
            $this->formDataFactory->createFormData($formDefinition, $formData),
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
