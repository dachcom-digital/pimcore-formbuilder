<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Type\DynamicFormType;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\FormFieldInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds a dynamic form.
 */
class Builder
{
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
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * Builder constructor.
     *
     * @param Configuration         $configuration
     * @param RequestStack          $requestStack
     * @param FormManager           $formManager
     * @param FormFactory           $formFactory
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        Configuration $configuration,
        RequestStack $requestStack,
        FormManager $formManager,
        FormFactory $formFactory,
        UrlGeneratorInterface $router
    ) {
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
        $this->formManager = $formManager;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function buildByRequest(Request $request)
    {
        foreach ($request->request->all() as $key => $parameters) {

            if (strpos($key, 'formbuilder_') === FALSE) {
                continue;
            }

            if (isset($parameters['formId'])) {
                return [$parameters['formId'], $this->buildForm($parameters['formId'])];
            }
        }

        return [NULL, NULL];
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
            'formTemplate' => 'default'
        ];

        $formOptions = array_merge($defaults, $userOptions);

        $request = $this->requestStack->getCurrentRequest();
        $formEntity = $this->formManager->getById($id);
        $formConfig = $formEntity->getConfig();

        $formTypes = $this->configuration->getConfig('types');
        $formConstraints = $this->configuration->getConfig('validation_constraints');

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

        /** @var FormFieldInterface $field */
        foreach ($formEntity->getFields() as $field) {

            $options = $field->getOptions();
            $optional = $field->getOptional();

            //set optional template
            if (isset($optional['template'])) {
                $options['attr']['data-template'] = $optional['template'];
            }

            $constraints = [];
            foreach ($field->getConstraints() as $constraint) {

                if (!isset($formConstraints[$constraint['type']])) {
                    continue;
                }

                $class = $formConstraints[$constraint['type']]['class'];
                $constraints[] = new $class();
            }

            if (!empty($constraints)) {
                $options['constraints'] = $constraints;
            }

            $builder->add(
                $field->getName(),
                $formTypes[$field->getType()]['class'],
                $options
            );
        }

        $form = $builder->getForm();

        // Handle request
        $form->handleRequest($request);

        return $form;
    }
}
