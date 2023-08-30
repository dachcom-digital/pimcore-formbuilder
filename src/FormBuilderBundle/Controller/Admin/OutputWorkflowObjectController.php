<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\OutputWorkflowManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\OutputWorkflow\DynamicObjectResolver\DynamicObjectResolverInterface;
use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Model\DataObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OutputWorkflowObjectController extends AdminAbstractController
{
    public function __construct(
        protected Configuration $configuration,
        protected FormFactoryInterface $formFactory,
        protected FormDefinitionManager $formDefinitionManager,
        protected OutputWorkflowManager $outputWorkflowManager,
        protected DynamicObjectResolverRegistry $dynamicObjectResolverRegistry,
        protected ExtJsFormBuilder $extJsFormBuilder
    ) {
    }

    public function getObjectClassesAction(Request $request): JsonResponse
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
     * @throws \Exception
     */
    public function getObjectClassesFieldsAction(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $id = $request->get('id');

        $fields = [];
        if ($type === 'fieldCollection') {
            $fieldCollectionDefinition = DataObject\Fieldcollection\Definition::getByKey($id);
            $fields = $fieldCollectionDefinition->getFieldDefinitions();
        } elseif ($type === 'dataClass') {
            $classDefinition = DataObject\ClassDefinition::getById($id);
            $fields = $classDefinition->getFieldDefinitions();
        }

        $flattenFields = [];
        foreach ($fields as $field) {
            $flattenFields[] = [
                'key'   => $field->getName(),
                'label' => empty($field->getTitle()) ? $field->getName() : $field->getTitle(),
            ];
        }

        return $this->adminJson([
            'success' => true,
            'fields'  => $flattenFields
        ]);
    }

    public function getFieldCollectionTypesForDataTypeAction(Request $request): JsonResponse
    {
        $classId = $request->get('classId');
        $fieldCollectionKey = $request->get('fieldCollectionKey');

        try {
            $classDefinition = DataObject\ClassDefinition::getById($classId);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        if (!$classDefinition instanceof DataObject\ClassDefinition) {
            return $this->json(['success' => false, 'message' => 'No class definition found.']);
        }

        $classDefinitionField = $classDefinition->getFieldDefinition($fieldCollectionKey);
        if (!$classDefinitionField instanceof DataObject\ClassDefinition\Data\Fieldcollections) {
            return $this->json(['success' => false, 'message' => 'No valid field field collection found.']);
        }

        $allowedTypes = [];
        foreach ($classDefinitionField->getAllowedTypes() as $type) {
            $allowedTypes[] = [
                'key'   => $type,
                'label' => $type
            ];
        }

        return $this->adminJson([
            'success' => true,
            'types'   => $allowedTypes
        ]);
    }

    public function getFormDataAction(Request $request): JsonResponse
    {
        $formId = $request->get('id');
        $baseConfiguration = json_decode($request->get('baseConfiguration', ''), true);

        $classDefinition = null;
        $formDefinition = $this->formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        $resolveStrategy = $baseConfiguration['resolveStrategy'];
        $dynamicObjectResolver = $baseConfiguration['dynamicObjectResolver'] ?? null;

        if (!empty($dynamicObjectResolver)) {
            $classDefinition = DataObject\ClassDefinition::getByName($baseConfiguration['dynamicObjectResolverClass'] ?? null);
        } elseif ($resolveStrategy === 'newObject') {
            $classDefinition = DataObject\ClassDefinition::getByName($baseConfiguration['resolvingObjectClass']);
        } elseif ($resolveStrategy === 'existingObject') {
            $dataObject = DataObject::getById($baseConfiguration['resolvingObject']['id']);
            $classDefinition = $dataObject instanceof DataObject\Concrete ? $dataObject->getClass() : null;
        }

        if (!$classDefinition instanceof DataObject\ClassDefinition) {
            return $this->json(['success' => false, 'message' => 'No class definition found.']);
        }

        $configuration = [
            'classId'   => $classDefinition->getId(),
            'className' => $classDefinition->getName()
        ];

        try {
            $extJsFormFields = $this->extJsFormBuilder->generateExtJsFormFields($formDefinition);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        $configuration['formFieldDefinitions'] = $extJsFormFields;

        return $this->adminJson([
            'success'       => true,
            'configuration' => $configuration
        ]);
    }

    public function getDynamicObjectResolverAction(Request $request): JsonResponse
    {
        $objectResolverModeFilter = $request->query->get('allowedObjectResolverMode', null);

        $data = [];
        foreach ($this->dynamicObjectResolverRegistry->getAll() as $identifier => $serviceData) {
            /** @var DynamicObjectResolverInterface $service */
            $service = $serviceData['service'];
            $allowedObjectResolverModes = $service::getAllowedObjectResolverModes();

            if ($objectResolverModeFilter !== null && !in_array($objectResolverModeFilter, $allowedObjectResolverModes)) {
                continue;
            }

            $data[] = [
                'label'                      => $serviceData['label'],
                'key'                        => $identifier,
                'allowedObjectResolverModes' => $allowedObjectResolverModes
            ];
        }

        return $this->adminJson([
            'success'  => true,
            'resolver' => $data
        ]);
    }
}
