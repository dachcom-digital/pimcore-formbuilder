<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\OutputWorkflowManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\DataObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OutputWorkflowObjectController extends AdminController
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormDefinitionManager
     */
    protected $formDefinitionManager;

    /**
     * @var OutputWorkflowManager
     */
    protected $outputWorkflowManager;

    /**
     * @var ExtJsFormBuilder
     */
    protected $extJsFormBuilder;

    /**
     * @param Configuration         $configuration
     * @param FormFactoryInterface  $formFactory
     * @param FormDefinitionManager $formDefinitionManager
     * @param OutputWorkflowManager $outputWorkflowManager
     * @param ExtJsFormBuilder      $extJsFormBuilder
     */
    public function __construct(
        Configuration $configuration,
        FormFactoryInterface $formFactory,
        FormDefinitionManager $formDefinitionManager,
        OutputWorkflowManager $outputWorkflowManager,
        ExtJsFormBuilder $extJsFormBuilder
    ) {
        $this->configuration = $configuration;
        $this->formFactory = $formFactory;
        $this->formDefinitionManager = $formDefinitionManager;
        $this->outputWorkflowManager = $outputWorkflowManager;
        $this->extJsFormBuilder = $extJsFormBuilder;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getObjectClassesAction(Request $request)
    {
        $list = new DataObject\ClassDefinition\Listing();

        $classList = [];
        foreach ($list->load() as $class) {
            $classList[] = [
                'key'   => $class->getName(),
                'label' => $class->getName()
            ];
        }

        return $this->adminJson([
            'success' => true,
            'types'   => $classList
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFormDataAction(Request $request)
    {
        $formId = $request->get('id');
        $baseConfiguration = json_decode($request->get('baseConfiguration', ''), true);

        $formDefinition = $this->formDefinitionManager->getById($formId);
        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        $resolveStrategy = $baseConfiguration['resolveStrategy'];
        $configuration = [];

        if ($resolveStrategy === 'newObject') {
            $resolvingObjectClass = $baseConfiguration['resolvingObjectClass'];
            $classDefinition = DataObject\ClassDefinition::getByName($resolvingObjectClass);
            $configuration = ['classId' => $classDefinition->getId(), 'className' => $classDefinition->getName()];
        } elseif ($resolveStrategy === 'existingObject') {
            $dataObject = DataObject::getById($baseConfiguration['resolvingObject']['id']);
            $classDefinition = $dataObject->getClass();
            $configuration = ['classId' => $classDefinition->getId(), 'className' => $classDefinition->getName()];
        }

        try {
            $extJsFormFields = $this->extJsFormBuilder->generateExtJsFormFields($formDefinition);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        $configuration['formFieldDefinitions'] = $extJsFormFields;

        return $this->adminJson([
            'success'              => true,
            'configuration'        => $configuration
        ]);
    }
}
