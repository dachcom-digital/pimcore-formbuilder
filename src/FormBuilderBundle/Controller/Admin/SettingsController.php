<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Backend\Form\Builder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Registry\ChoiceBuilderRegistry;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

class SettingsController extends AdminController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTreeAction(Request $request)
    {
        $forms = $this->get(FormManager::class)->getAll();

        $mainItems = [];
        /** @var \FormBuilderBundle\Storage\Form $form */
        foreach ($forms as $form) {
            $mainItems[] = [

                'id'            => (int)$form->getId(),
                'text'          => $form->getName(),
                'icon'          => '',
                'leaf'          => true,
                'iconCls'       => 'form_builder_icon_root',
                'allowChildren' => false

            ];
        }

        return $this->json($mainItems);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSettingsAction(Request $request)
    {
        /** @var Configuration $configuration */
        $configuration = $this->container->get(Configuration::class);
        $settings = $configuration->getConfigArray();
        $settings['forbidden_form_field_names'] = Configuration::INVALID_FIELD_NAMES;
        return $this->json(['settings' => $settings]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getDynamicChoiceBuilderAction(Request $request)
    {
        $registry = $this->container->get(ChoiceBuilderRegistry::class);
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
     * @return JsonResponse
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
            $data['data'] = $backendFormBuilder->generateExtJsForm($form);
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
     * @return JsonResponse
     */
    public function addFormAction(Request $request)
    {
        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        $name = $this->getSaveName($request->query->get('form_name'));

        $error = false;
        $message = '';
        $id = null;

        $existingForm = false;

        try {
            $existingForm = $formManager->getIdByName($name);
        } catch (\Exception $e) {
        }

        if (!empty($existingForm)) {
            $error = true;
            $message = 'Form already exists!';
        } else {

            $data = [
                'form_name' => $name,
                'form_date' => time()
            ];

            $formEntity = $formManager->save($data);
            $id = $formEntity->getId();
        }

        return $this->json(
            [
                'success' => !$error,
                'message' => $message,
                'id'      => (int)$id,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteFormAction(Request $request)
    {
        $id = $request->get('id');
        $this->get(FormManager::class)->delete($id);

        return $this->json(
            [
                'success' => true,
                'id'      => (int)$id,
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function saveFormAction(Request $request)
    {
        $id = $request->get('form_id');

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        /** @var Builder $backendFormBuilder */
        $backendFormBuilder = $this->get(Builder::class);

        $formEntity = $formManager->getById($id);
        $storedFormName = $formEntity->getName();

        $formName = $request->get('form_name');
        $formConfig = json_decode($request->get('form_config'), true);
        $formFields = json_decode($request->get('form_fields'), true);
        $formConditionalLogic = json_decode($request->get('form_cl'), true);
        if (isset($formConditionalLogic['cl'])) {
            $formConditionalLogic = $formConditionalLogic['cl'];
        }

        if ($formName != $storedFormName) {
            $formName = $this->getSaveName($formName);
            $formManager->rename($id, $formName);
        }

        $data = [
            'form_name'              => $formName,
            //'form_date'            => time(),
            'form_fields'            => $backendFormBuilder->generateStoreFields($formFields),
            'form_config'            => $formConfig,
            'form_conditional_logic' => $formConditionalLogic,
        ];

        $formEntity = $formManager->save($data, $id);

        return $this->json([
            'formId'   => (int)$id,
            'formName' => $formEntity->getName(),
            'success'  => true
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function importFormAction(Request $request)
    {
        $data = file_get_contents($_FILES['Filedata']['tmp_name']);

        $encoding = \Pimcore\Tool\Text::detectEncoding($data);

        if ($encoding) {
            $data = iconv($encoding, 'UTF-8', $data);
        }

        if (!is_dir(Configuration::IMPORT_PATH)) {
            mkdir(Configuration::IMPORT_PATH);
        }

        $importFile = Configuration::IMPORT_PATH . '/import_' . $request->get('id');

        file_put_contents($importFile, $data);

        chmod($importFile, 0766);

        $res = [];
        $res['success'] = true;

        return $this->json(
            [
                'success' => true,
                'msg'     => $res['success'] ? 'Success' : 'Error',
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function getImportFileAction(Request $request)
    {
        $formId = $request->get('id');

        /** @var Builder $backendFormBuilder */
        $backendFormBuilder = $this->get(Builder::class);

        if (!file_exists(Configuration::IMPORT_PATH . '/import_' . $formId)) {
            throw new NotFoundHttpException('no import form with id ' . $formId . ' found.');
        }

        $data = file_get_contents(Configuration::IMPORT_PATH . '/import_' . $formId);
        $formContent = Yaml::parse($data);
        $formContent['fields'] = $backendFormBuilder->generateExtJsFields($formContent['fields']);

        unlink(Configuration::IMPORT_PATH . '/import_' . $formId);

        return $this->json([
            'data' => $formContent
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getExportFileAction(Request $request)
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
     * @return JsonResponse
     */
    public function checkPathAction(Request $request)
    {
        $path = $request->get('path');
        $pathIsValid = is_dir(PIMCORE_PUBLIC_VAR . '/' . ltrim($path, '/'));

        return $this->json(['success' => $pathIsValid]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getGroupTemplatesAction(Request $request)
    {
        /** @var Configuration $configuration */
        $configuration = $this->container->get(Configuration::class);
        $areaConfig = $configuration->getConfig('area');

        $templates = [['key' => null, 'label' => '--']];

        foreach ($areaConfig['group_templates'] as $configName => $element) {
            $templates[] = ['key' => $configName, 'label' => $element['niceName']];
        }

        return $this->json($templates);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    private function getSaveName($name = '')
    {
        return preg_replace('/[^A-Za-z0-9aäüöÜÄÖß \-]/', '', $name);
    }

}
