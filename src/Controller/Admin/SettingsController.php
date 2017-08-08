<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Backend\Form\Builder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\FormManager;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;

class SettingsController extends AdminController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTreeAction(Request $request)
    {
        $forms = $this->get('form_builder.manager.form')->getAll();

        $mainItems = [];
        /** @var \FormBuilderBundle\Storage\Form $form */
        foreach ($forms as $form) {
            $mainItems[] = [

                'id'            => (int)$form->getId(),
                'text'          => $form->getName(),
                'icon'          => '',
                'leaf'          => TRUE,
                'iconCls'       => 'Formbuilder_icon_root',
                'allowChildren' => FALSE

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
        $configuration = $this->container->get('form_builder.configuration');

        return $this->json(['settings' => $configuration->getConfigArray()]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getFormAction(Request $request)
    {
        $id = $request->query->get('id');

        /** @var Builder $backendFormBuilder */
        $backendFormBuilder = $this->get('form_builder.backend.form_builder');

        try {
            $form = $this->get('form_builder.manager.form')->getById($id);
            $data = $backendFormBuilder->generateExtJsForm($form);
        } catch (\Exception $e) {
            $data = ['success' => FALSE, 'message' => $e->getMessage()];
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
        $formManager = $this->get('form_builder.manager.form');

        $name = $this->getSaveName($request->query->get('form_name'));

        $error = FALSE;
        $message = '';
        $id = NULL;

        $existingForm = FALSE;

        try {
            $existingForm = $formManager->getIdByName($name);
        } catch (\Exception $e) {
        }

        if (!empty($existingForm)) {
            $error = TRUE;
            $message = 'Form already exists!';
        } else {

            $data = [
                'form_name' => $name,
                'form_date' => time()
            ];

            $formEntity = $this->get('form_builder.manager.form')->save($data);
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
        $this->get('form_builder.manager.form')->delete($id);

        return $this->json(
            [
                'success' => TRUE,
                'id'      => (int)$id,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function saveFormAction(Request $request)
    {
        $id = $request->get('form_id');

        /** @var FormManager $formManager */
        $formManager = $this->get('form_builder.manager.form');

        $formEntity = $formManager->getById($id);
        $storedFormName = $formEntity->getName();

        $formName = $request->get('form_name');
        $formConfig = json_decode($request->get('form_config'), TRUE);
        $formFields = json_decode($request->get('form_fields'), TRUE);

        if ($formName != $storedFormName) {
            $formName = $this->getSaveName($formName);
            $formManager->rename($id, $formName);
        }

        $data = [
            'form_name'   => $formName,
            //'form_date'   => time(),
            'form_fields' => $formFields,
            'form_config' => $formConfig,
        ];

        $formEntity = $formManager->save($data, $id);

        return $this->json([
            'formId'   => (int)$id,
            'formName' => $formEntity->getName(),
            'success'  => TRUE
        ]);
    }

    /**
     * @fixme: pimcore5
     *
     * @param Request $request
     */
    public function importFormAction(Request $request)
    {
        $data = file_get_contents($_FILES['Filedata']['tmp_name']);

        $encoding = \Pimcore\Tool\Text::detectEncoding($data);

        if ($encoding) {
            $data = iconv($encoding, 'UTF-8', $data);
        }

        if (!is_dir(FORMBUILDER_DATA_PATH . '/import/')) {
            mkdir(FORMBUILDER_DATA_PATH . '/import/');
        }

        $importFile = FORMBUILDER_DATA_PATH . '/import/import_' . $this->getParam('id');

        file_put_contents($importFile, $data);

        chmod($importFile, 0766);

        $res = [];
        $res['success'] = TRUE;

        $this->_helper->json(
            [
                'success' => TRUE,
                'msg'     => $res['success'] ? 'Success' : 'Error',
            ]
        );
    }

    /**
     * @fixme: pimcore5
     *
     * @param Request $request
     */
    public function getImportFileAction(Request $request)
    {
        $id = $this->_getParam('id');

        if (file_exists(FORMBUILDER_DATA_PATH . '/import/import_' . $id)) {
            $config = new Zend_Config_Json(FORMBUILDER_DATA_PATH . '/import/import_' . $id);

            unlink(FORMBUILDER_DATA_PATH . '/import/import_' . $id);

            $data = $config->toArray();
            unset($data['name'], $data['id']);

            $this->_helper->json($data);
        } else {
            $this->_helper->json(NULL);
        }
    }

    /**
     * @fixme: pimcore5
     *
     * @param Request $request
     */
    public function getExportFileAction(Request $request)
    {
        $id = $this->getParam('id');
        $name = $this->getParam('name');

        if (is_numeric($id)) {
            $exportName = 'export_' . $name;

            $exportFile = FORMBUILDER_DATA_PATH . '/main_' . $id . '.json';

            $this->getResponse()->setHeader('Content-Type', 'application/json', TRUE);
            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="form_' . $exportName . '.json"');

            echo file_get_contents($exportFile);
        }

        $this->removeViewRenderer();
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
        $configuration = $this->container->get('form_builder.configuration');
        $areaConfig = $configuration->getConfig('area');

        $templates = [['key' => NULL, 'label' => '--']];

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
