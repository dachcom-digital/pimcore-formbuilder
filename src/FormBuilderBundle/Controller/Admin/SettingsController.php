<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Backend\Form\Builder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Registry\ChoiceBuilderRegistry;
use FormBuilderBundle\Storage\FormInterface;
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
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getTreeAction()
    {
        $forms = $this->get(FormManager::class)->getAll();

        $mainItems = [];
        /** @var \FormBuilderBundle\Storage\Form $form */
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
                    'id'            => (int)$form->getId(),
                    'text'          => $form->getName(),
                    'icon'          => '',
                    'leaf'          => true,
                    'iconCls'       => 'form_builder_icon_root',
                    'allowChildren' => false
                ];
            } else {
                $mainItems[] = [
                    'id'            => (int)$form->getId(),
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

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSettingsAction()
    {
        /** @var Configuration $configuration */
        $configuration = $this->get(Configuration::class);
        $settings = $configuration->getConfigArray();
        $settings['forbidden_form_field_names'] = Configuration::INVALID_FIELD_NAMES;

        return $this->json(['settings' => $settings]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDynamicChoiceBuilderAction()
    {
        $registry = $this->get(ChoiceBuilderRegistry::class);
        $services = $registry->getAll();
        $data = [];
        foreach ($services as $identifier => $service) {
            $data[] = ['label' => $service['label'], 'value' => $identifier];
        }

        return $this->json($data);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFormAction(Request $request)
    {
        $id = $request->query->get('id');

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        /** @var Builder $backendFormBuilder */
        $backendFormBuilder = $this->get(Builder::class);

        $data = [
            'success' => true,
            'message' => null
        ];

        try {
            $form = $formManager->getById($id);
            if ($form instanceof FormInterface) {
                $data['data'] = $backendFormBuilder->generateExtJsForm($form);
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

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addFormAction(Request $request)
    {
        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        $name = $this->getSaveName($request->query->get('form_name'));

        $success = true;
        $message = null;
        $id = null;

        try {
            $existingForm = $formManager->getIdByName($name);
        } catch (\Exception $e) {
            $existingForm = null;
        }

        if ($existingForm instanceof FormInterface) {
            $success = false;
            $message = sprintf('Form with name "%s" already exists!', $name);
        } else {
            try {
                $formEntity = $formManager->save(['form_name' => $name]);
                $id = $formEntity->getId();
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

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteFormAction(Request $request)
    {
        $id = $request->get('id');
        $success = true;
        $message = null;

        try {
            $this->get(FormManager::class)->delete($id);
        } catch (\Exception $e) {
            $success = false;
            $message = sprintf('Error while deleting form with id %d. Error was: %s', $id, $e->getMessage());
        }

        return $this->json([
            'success' => $success,
            'message' => $message,
            'id'      => (int)$id,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function saveFormAction(Request $request)
    {
        $id = (int)$request->get('form_id');
        $success = true;
        $message = null;

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        /** @var Builder $backendFormBuilder */
        $backendFormBuilder = $this->get(Builder::class);

        $formEntity = $formManager->getById($id);
        $storedFormName = $formEntity->getName();

        $formConfig = json_decode($request->get('form_config'), true);
        $formFields = json_decode($request->get('form_fields'), true);

        $formConditionalLogic = json_decode($request->get('form_cl'), true);
        if (isset($formConditionalLogic['cl'])) {
            $formConditionalLogic = $formConditionalLogic['cl'];
        }

        $formName = (string)$formConfig['name'];
        $formGroup = (string)$formConfig['group'];

        if ($formName !== $storedFormName) {

            try {
                $existingForm = $formManager->getIdByName($formName);
            } catch (\Exception $e) {
                $existingForm = null;
            }

            if ($existingForm instanceof FormInterface) {
                return $this->json([
                    'success' => false,
                    'message' => sprintf('Form with name "%s" already exists!', $formName)
                ]);
            }

            $formName = $this->getSaveName($formName);
            $formManager->rename($id, $formName);
        }

        $data = [
            'form_name'              => $formName,
            'form_group'             => $formGroup,
            'form_config'            => $formConfig,
            'form_fields'            => $backendFormBuilder->generateStoreFields($formFields),
            'form_conditional_logic' => $backendFormBuilder->generateConditionalLogicStoreFields($formConditionalLogic),
        ];

        try {
            $formEntity = $formManager->save($data, $id);
        } catch (\Exception $e) {
            $success = false;
            $message = sprintf('Error while saving form with id %d. Error was: %s', $id, $e->getMessage());
        }

        return $this->json([
            'formId'   => (int)$id,
            'formName' => $formEntity->getName(),
            'success'  => $success,
            'message'  => $message
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function importFormAction(Request $request)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('formData');
        $data = file_get_contents($file->getPathname());
        $encoding = \Pimcore\Tool\Text::detectEncoding($data);

        if ($encoding) {
            $data = iconv($encoding, 'UTF-8', $data);
        }

        /** @var Builder $backendFormBuilder */
        $backendFormBuilder = $this->get(Builder::class);

        $response = [
            'success' => true,
            'data'    => [],
            'message' => 'Success!',
        ];

        try {
            $formContent = Yaml::parse($data);
            $formContent['fields'] = $backendFormBuilder->generateExtJsFields($formContent['fields']);
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

        $response = new JsonResponse($jsonData, 200, ['Content-Type' => 'text/plain'], true);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportFormAction(Request $request)
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

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function checkPathAction(Request $request)
    {
        $path = $request->get('path');
        $pathIsValid = is_dir(PIMCORE_PUBLIC_VAR . '/' . ltrim($path, '/'));

        return $this->json(['success' => $pathIsValid]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getGroupTemplatesAction()
    {
        /** @var Configuration $configuration */
        $configuration = $this->get(Configuration::class);
        $areaConfig = $configuration->getConfig('area');

        $templates = [['key' => null, 'label' => '--']];

        foreach ($areaConfig['group_templates'] as $configName => $element) {
            $templates[] = ['key' => $configName, 'label' => $element['niceName']];
        }

        return $this->json($templates);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function findFormDependenciesAction(Request $request)
    {
        $formId = (int)$request->get('formId');
        $offset = (int)$request->get('start', 0);
        $limit = (int)$request->get('limit', 25);

        $dependencyLocator = $this->get(FormDependencyLocator::class);

        try {
            $data = $dependencyLocator->findDocumentDependencies($formId, $offset, $limit);
        } catch (\Exception $e) {
            $data = [];
        }

        return $this->json([
            'documents' => isset($data['documents']) ? $data['documents'] : [],
            'limit'     => $limit,
            'total'     => isset($data['total']) ? $data['total'] : 0
        ]);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getSaveName($name)
    {
        return (string)preg_replace('/[^A-Za-z0-9aäüöÜÄÖß \-]/', '', $name);
    }

}
