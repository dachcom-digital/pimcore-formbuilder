<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\PresetManager;
use FormBuilderBundle\Registry\ChoiceBuilderRegistry;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Tool\FormDependencyLocator;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

class SettingsController extends AdminController
{
    protected Configuration $configuration;
    protected FormDefinitionManager $formDefinitionManager;
    protected ExtJsFormBuilder $extJsFormBuilder;
    protected ChoiceBuilderRegistry $choiceBuilderRegistry;
    protected FormDependencyLocator $formDependencyLocator;

    public function __construct(
        Configuration $configuration,
        FormDefinitionManager $formDefinitionManager,
        ExtJsFormBuilder $extJsFormBuilder,
        ChoiceBuilderRegistry $choiceBuilderRegistry,
        FormDependencyLocator $formDependencyLocator
    ) {
        $this->configuration = $configuration;
        $this->formDefinitionManager = $formDefinitionManager;
        $this->extJsFormBuilder = $extJsFormBuilder;
        $this->choiceBuilderRegistry = $choiceBuilderRegistry;
        $this->formDependencyLocator = $formDependencyLocator;
    }

    public function getTreeAction(): JsonResponse
    {
        $forms = $this->formDefinitionManager->getAll();

        $mainItems = [];
        foreach ($forms as $form) {
            if (!is_null($form->getGroup())) {
                if (array_search($form->getGroup(), array_column($mainItems, 'id')) === false) {
                    $mainItems[] = [
                        'id'            => $form->getGroup(),
                        'text'          => $form->getGroup(),
                        'leaf'          => false,
                        'expandable'    => true,
                        'allowChildren' => true,
                        'iconCls'       => 'pimcore_icon_folder',
                        'children'      => []
                    ];
                }

                $groupKey = array_search($form->getGroup(), array_column($mainItems, 'id'));

                $mainItems[$groupKey]['children'][] = [
                    'id'            => (int) $form->getId(),
                    'text'          => $form->getName(),
                    'icon'          => '',
                    'leaf'          => true,
                    'iconCls'       => 'form_builder_icon_root',
                    'allowChildren' => false
                ];
            } else {
                $mainItems[] = [
                    'id'            => (int) $form->getId(),
                    'text'          => $form->getName(),
                    'icon'          => '',
                    'leaf'          => true,
                    'iconCls'       => 'form_builder_icon_root',
                    'allowChildren' => false
                ];
            }
        }

        return $this->json($mainItems);
    }

    public function getSettingsAction(): JsonResponse
    {
        $settings = $this->configuration->getConfigArray();
        $honeypotFieldName = $settings['spam_protection']['honeypot']['field_name'];
        $settings['forbidden_form_field_names'] = array_merge(Configuration::INVALID_FIELD_NAMES, [$honeypotFieldName]);

        return $this->json(['settings' => $settings]);
    }

    public function getDynamicChoiceBuilderAction(): JsonResponse
    {
        $services = $this->choiceBuilderRegistry->getAll();
        $data = [];
        foreach ($services as $identifier => $service) {
            $data[] = ['label' => $service['label'], 'value' => $identifier];
        }

        return $this->json($data);
    }

    public function getFormAction(Request $request): JsonResponse
    {
        $id = $request->query->get('id');

        $data = [
            'success' => true,
            'message' => null
        ];

        try {
            $form = $this->formDefinitionManager->getById($id);
            if ($form instanceof FormDefinitionInterface) {
                $data['data'] = $this->extJsFormBuilder->generateExtJsForm($form);
            } else {
                throw new \Exception(sprintf('No form for id %d found.', $id));
            }
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => $e->getMessage() . ' (' . $e->getFile() . ': ' . $e->getLine() . ')'
            ];
        }

        return $this->json($data);
    }

    public function addFormAction(Request $request): JsonResponse
    {
        $name = $this->getSaveName($request->query->get('form_name'));

        $success = true;
        $message = null;
        $id = null;

        try {
            $existingForm = $this->formDefinitionManager->getIdByName($name);
        } catch (\Exception $e) {
            $existingForm = null;
        }

        if ($existingForm instanceof FormDefinitionInterface) {
            $success = false;
            $message = sprintf('Form with name "%s" already exists!', $name);
        } else {
            try {
                $formDefinition = $this->formDefinitionManager->save(['form_name' => $name]);
                $id = $formDefinition->getId();
            } catch (\Exception $e) {
                $success = false;
                $message = sprintf('Error while creating new form with name "%s". Error was: %s', $name, $e->getMessage());
            }
        }

        return $this->json([
            'success' => $success,
            'message' => $message,
            'id'      => $id,
        ]);
    }

    public function deleteFormAction(Request $request): JsonResponse
    {
        $id = $request->get('id');
        $success = true;
        $message = null;

        try {
            $this->formDefinitionManager->delete($id);
        } catch (\Exception $e) {
            $success = false;
            $message = sprintf('Error while deleting form with id %d. Error was: %s', $id, $e->getMessage());
        }

        return $this->json([
            'success' => $success,
            'message' => $message,
            'id'      => (int) $id,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function saveFormAction(Request $request): JsonResponse
    {
        $id = (int) $request->get('form_id');
        $success = true;
        $message = null;

        $formDefinition = $this->formDefinitionManager->getById($id);
        $storedFormName = $formDefinition->getName();

        $formConfig = json_decode($request->get('form_config'), true);
        $formFields = json_decode($request->get('form_fields'), true);

        $formConditionalLogic = json_decode($request->get('form_cl'), true);
        if (isset($formConditionalLogic['cl'])) {
            $formConditionalLogic = $formConditionalLogic['cl'];
        }

        $formName = (string) $formConfig['name'];
        $formGroup = (string) $formConfig['group'];

        if ($formName !== $storedFormName) {
            try {
                $existingForm = $this->formDefinitionManager->getIdByName($formName);
            } catch (\Exception $e) {
                $existingForm = null;
            }

            if ($existingForm instanceof FormDefinitionInterface) {
                return $this->json([
                    'success' => false,
                    'message' => sprintf('Form with name "%s" already exists!', $formName)
                ]);
            }

            $formName = $this->getSaveName($formName);
            $this->formDefinitionManager->rename($id, $formName);
        }

        $data = [
            'form_name'              => $formName,
            'form_group'             => $formGroup,
            'form_config'            => $formConfig,
            'form_fields'            => $this->extJsFormBuilder->generateStoreFields($formFields),
            'form_conditional_logic' => $this->extJsFormBuilder->generateConditionalLogicStoreFields($formConditionalLogic),
        ];

        try {
            $formDefinition = $this->formDefinitionManager->save($data, $id);
        } catch (\Exception $e) {
            $success = false;
            $message = sprintf('Error while saving form with id %d. Error was: %s', $id, $e->getMessage());
        }

        return $this->json([
            'formId'   => (int) $id,
            'formName' => $formDefinition->getName(),
            'success'  => $success,
            'message'  => $message
        ]);
    }

    /**
     * @throws \Exception
     */
    public function importFormAction(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('formData');
        $data = file_get_contents($file->getPathname());
        $encoding = \Pimcore\Tool\Text::detectEncoding($data);

        if ($encoding) {
            $data = iconv($encoding, 'UTF-8', $data);
        }

        $response = [
            'success' => true,
            'data'    => [],
            'message' => 'Success!',
        ];

        try {
            $formContent = Yaml::parse($data);
            $formContent['fields'] = $this->extJsFormBuilder->generateExtJsFields($formContent['fields']);
            $response['data'] = $formContent;
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        if (!$this->container->has('serializer')) {
            throw new \LogicException('No serializer found.');
        }

        $jsonData = $this->container->get('serializer')->serialize($response, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], []));

        return new JsonResponse($jsonData, 200, ['Content-Type' => 'text/plain'], true);
    }

    public function exportFormAction(Request $request): Response
    {
        $formId = $request->get('id');

        if (!is_numeric($formId)) {
            throw new NotFoundHttpException('no form with id ' . $formId . ' found.');
        }

        $exportName = 'form_export_' . $formId . '.yml';
        $exportFile = Configuration::STORE_PATH . '/main_' . $formId . '.yml';
        if (!file_exists($exportFile)) {
            throw new NotFoundHttpException('no form configuration with id ' . $formId . ' found.');
        }

        $response = new Response(file_get_contents($exportFile));
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $exportName
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function getGroupTemplatesAction(): JsonResponse
    {
        $areaConfig = $this->configuration->getConfig('area');

        $templates = [['key' => null, 'label' => '--']];

        foreach ($areaConfig['group_templates'] as $configName => $element) {
            $templates[] = ['key' => $configName, 'label' => $element['niceName']];
        }

        return $this->json($templates);
    }

    public function findFormDependenciesAction(Request $request): JsonResponse
    {
        $formId = (int) $request->get('formId');
        $offset = (int) $request->get('start', 0);
        $limit = (int) $request->get('limit', 25);

        try {
            $data = $this->formDependencyLocator->findDocumentDependencies($formId, $offset, $limit);
        } catch (\Exception $e) {
            $data = [];
        }

        return $this->json([
            'documents' => $data['documents'] ?? [],
            'limit'     => $limit,
            'total'     => $data['total'] ?? 0
        ]);
    }

    public function getPresetDescriptionAction(Request $request, PresetManager $presetManager, string $name): JsonResponse
    {
        $preset = $presetManager->getDataForPreview($name);

        return $this->json(
            [
                'success'     => true,
                'previewData' => $preset
            ]
        );
    }

    private function getSaveName(string $name): string
    {
        return (string) preg_replace('/[^A-Za-z0-9aäüöÜÄÖß \-]/', '', $name);
    }
}
