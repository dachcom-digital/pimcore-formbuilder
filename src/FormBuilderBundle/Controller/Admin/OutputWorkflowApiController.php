<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Registry\ApiProviderRegistry;
use FormBuilderBundle\Registry\FieldTransformerRegistry;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowApiController extends AdminController
{
    protected FormDefinitionManager $formDefinitionManager;
    protected ExtJsFormBuilder $extJsFormBuilder;
    protected ApiProviderRegistry $apiProviderRegistry;
    protected FieldTransformerRegistry $fieldTransformerRegistry;

    public function __construct(
        FormDefinitionManager $formDefinitionManager,
        ExtJsFormBuilder $extJsFormBuilder,
        ApiProviderRegistry $apiProviderRegistry,
        FieldTransformerRegistry $fieldTransformerRegistry
    ) {
        $this->formDefinitionManager = $formDefinitionManager;
        $this->extJsFormBuilder = $extJsFormBuilder;
        $this->apiProviderRegistry = $apiProviderRegistry;
        $this->fieldTransformerRegistry = $fieldTransformerRegistry;
    }

    public function getFormDataAction(Request $request): JsonResponse
    {
        $formId = $request->get('id');
        $baseConfiguration = json_decode($request->get('baseConfiguration', ''), true);
        $formDefinition = $this->formDefinitionManager->getById($formId);

        $apiProviderName = $baseConfiguration['apiProvider'];
        $configurationFields = $baseConfiguration['apiConfiguration'] ?? [];

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        try {
            $extJsFormFields = $this->extJsFormBuilder->generateExtJsFormFields($formDefinition);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        try {
            $apiProvider = $this->apiProviderRegistry->get($apiProviderName);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => sprintf('API Provider error: %s', $e->getMessage())]);
        }

        try {
            $predefinedApiFields = $this->validateApPredefinedFields($apiProvider->getPredefinedApiFields($formDefinition, $configurationFields));
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        $fieldTransformer = [];
        foreach ($this->fieldTransformerRegistry->getAll() as $fieldTransformerIdentifier => $transformer) {
            $fieldTransformer[] = [
                'value'       => $fieldTransformerIdentifier,
                'label'       => $transformer->getName(),
                'description' => $transformer->getDescription(),
            ];
        }

        return $this->adminJson([
            'success'       => true,
            'configuration' => [
                'formFieldDefinitions' => $extJsFormFields,
                'fieldTransformer'     => $fieldTransformer,
                'apiProvider'          => [
                    'key'                 => $apiProviderName,
                    'label'               => $apiProvider->getName(),
                    'predefinedApiFields' => $predefinedApiFields
                ]
            ]
        ]);
    }

    public function getApiProviderAction(Request $request): JsonResponse
    {
        $data = [];
        $services = $this->apiProviderRegistry->getAll();

        $formId = $request->get('id');
        $formDefinition = $this->formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        foreach ($services as $identifier => $service) {

            try {
                $configurationFields = $this->validateApiConfigurationFields($service->getProviderConfigurationFields($formDefinition));
            } catch (\Throwable $e) {
                return $this->json(['success' => false, 'message' => $e->getMessage()]);
            }

            $data[] = [
                'label'               => $service->getName(),
                'key'                 => $identifier,
                'configurationFields' => $configurationFields
            ];
        }

        return $this->adminJson([
            'success' => true,
            'types'   => $data
        ]);
    }

    protected function validateApPredefinedFields(array $fields): array
    {
        return array_map(static function ($property) {
            return [
                'label' => $property,
                'value' => $property
            ];

        }, $fields);
    }

    protected function validateApiConfigurationFields(array $fields): array
    {
        if (count($fields) === 0) {
            return $fields;
        }

        $validatedConfigurationFields = [];

        foreach ($fields as $field) {

            $optionsResolver = new OptionsResolver();
            $optionsResolver->setRequired(['type', 'label', 'name', 'required']);
            $optionsResolver->setAllowedValues('type', ['text', 'select']);
            $optionsResolver->setAllowedTypes('type', ['string']);
            $optionsResolver->setAllowedTypes('label', ['string']);
            $optionsResolver->setAllowedTypes('name', ['string']);
            $optionsResolver->setAllowedTypes('required', ['bool']);

            if ($field['type'] === 'select') {
                $optionsResolver->setRequired('store');
                $optionsResolver->setAllowedTypes('store', ['array']);
            }

            try {
                $validatedConfigurationFields[] = $optionsResolver->resolve($field);
            } catch (\Throwable $e) {
                throw new \Exception(sprintf('API configuration error for field "%s": %s', $field['type'] ?? '-', $e->getMessage()));
            }
        }

        return $validatedConfigurationFields;
    }
}
