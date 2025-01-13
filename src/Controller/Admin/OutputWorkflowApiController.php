<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Registry\ApiProviderRegistry;
use FormBuilderBundle\Registry\FieldTransformerRegistry;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowApiController extends AdminAbstractController
{
    public function __construct(
        protected FormDefinitionManager $formDefinitionManager,
        protected ExtJsFormBuilder $extJsFormBuilder,
        protected ApiProviderRegistry $apiProviderRegistry,
        protected FieldTransformerRegistry $fieldTransformerRegistry
    ) {
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
            $predefinedApiFields = $this->validateApiPredefinedFields($apiProvider->getPredefinedApiFields($formDefinition, $configurationFields));
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

    protected function validateApiPredefinedFields(array $fields): array
    {
        return array_map(static function ($property) {
            if (!is_array($property)) {
                $property = [
                    'label' => $property,
                    'value' => $property,
                ];
            }

            return [
                'label' => $property['label'] ?? 'UNKNOWN',
                'value' => $property['value'] ?? 'UNKNOWN'
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
