<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Registry\FormTypeRegistry;
use FormBuilderBundle\Storage\FormField;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds a dynamic form.
 */
class Builder
{
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
     * @var FormTypeRegistry
     */
    protected $formTypeRegistry;

    /**
     * Builder constructor.
     *
     * @param RequestStack     $requestStack
     * @param FormManager      $formManager
     * @param FormFactory      $formFactory
     * @param FormTypeRegistry $formTypeRegistry
     */
    public function __construct(
        RequestStack $requestStack,
        FormManager $formManager,
        FormFactory $formFactory,
        FormTypeRegistry $formTypeRegistry
    ) {
        $this->requestStack = $requestStack;
        $this->formManager = $formManager;
        $this->formFactory = $formFactory;
        $this->formTypeRegistry = $formTypeRegistry;
    }

    public function buildByRequest(Request $request)
    {
        foreach ($request->request->all() as $key => $parameters) {

            if (strpos($key, 'formbuilder_') === FALSE) {
                continue;
            }

            if(isset($parameters['formId'])) {
                return [$parameters['formId'], $this->buildForm($parameters['formId'])];
            }
        }

        return [null, null];
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

        $formAttributes = [];
        if($formConfig['noValidate'] === FALSE) {
            $formAttributes['novalidate'] = 'novalidate';
        }

        //@todo: implement inline functionality.
        //$formAttributes['class'] = 'form-inline';

        $builder = $this->formFactory->createNamedBuilder(
            'formbuilder_' . $formEntity->getId(),
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            null,
            ['attr' => $formAttributes]
        );

        /** @var FormField $field */
        foreach ($formEntity->getFields() as $field) {
            $this->formTypeRegistry->get($field->getType())->build($builder, $field);
        }

        //@todo: encrypt?
        $builder->add('formId', HiddenType::class, [
            'data' => $formEntity->getId(),
        ]);

        // Add submit button.
        $builder->add('submit', SubmitType::class, ['label' => 'submit']);

        $form = $builder->getForm();

        // Handle request
        $form->handleRequest($request);

        return $form;
    }
}
