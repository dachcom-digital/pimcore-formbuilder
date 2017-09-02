<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\EventListener\Core\FormListener;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Type\DynamicFormType;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\FormFieldDynamicInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
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
     * @var
     */
    private $availableConstraints;

    /**
     * @var
     */
    private $availableFormTypes;

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
            'formPreset' => NULL
        ];

        $formOptions = array_merge($defaults, $userOptions);

        $this->setAvailableConstraints();
        $this->setAvailableFormTypes();

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
        $builder->addEventSubscriber(new FormListener($formOptions));

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            [$this, 'onPostSetData']
        );

        $form = $builder->getForm();

        // Handle request
        $form->handleRequest($request);

        return $form;
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $formEntity = $event->getData();

        $orderedFields = $formEntity->getFields();
        usort($orderedFields, function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        /** @var FormFieldInterface $field */
        foreach ($orderedFields as $field) {
            if ($field instanceof FormFieldDynamicInterface) {
                $this->addDynamicField($form, $field);
            } else {
                $this->addFormBuilderField($form, $field);
            }
        }
    }

    /**
     * @param FormInterface      $form
     * @param FormFieldInterface $field
     */
    private function addFormBuilderField(FormInterface $form, FormFieldInterface $field)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        $constraints = [];
        foreach ($field->getConstraints() as $constraint) {

            if (!isset($this->availableConstraints[$constraint['type']])) {
                continue;
            }

            $class = $this->availableConstraints[$constraint['type']]['class'];
            $constraints[] = new $class();
        }

        if (!empty($constraints)) {
            $options['constraints'] = $constraints;
        }

        $form->add(
            $field->getName(),
            $this->availableFormTypes[$field->getType()]['class'],
            $options
        );
    }

    /**
     * @param FormInterface             $form
     * @param FormFieldDynamicInterface $field
     */
    private function addDynamicField(FormInterface $form, FormFieldDynamicInterface $field)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        $form->add(
            $field->getName(),
            $field->getType(),
            $options
        );
    }

    private function setAvailableConstraints()
    {
        $this->availableConstraints = $this->configuration->getConfig('validation_constraints');
    }

    private function setAvailableFormTypes()
    {
        $this->availableFormTypes = $this->configuration->getConfig('types');
    }
}
