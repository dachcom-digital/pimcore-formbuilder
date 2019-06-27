<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Backend\Form\Builder;
use FormBuilderBundle\MailEditor\Widget\MailEditorFieldDataWidgetInterface;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MailEditorController extends AdminController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getMailEditorAvailableMailTypesAction(Request $request)
    {
        $formId = $request->get('id');

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        $formEntity = $formManager->getById($formId);

        if (!$formEntity instanceof FormInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        $mailLayouts = $formEntity->getMailLayout();

        $availableTypes = [];
        foreach (['main', 'copy'] as $validType) {
            $availableTypes[] =
                [
                    'identifier'  => $validType,
                    'isAvailable' => isset($mailLayouts[$validType])
                ];

        }

        return $this->json(['success' => true, 'types' => $availableTypes]);

    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteMailEditorMailTypeAction(Request $request)
    {
        $success = true;
        $message = null;

        $formId = $request->get('id');
        $mailType = $request->get('mailType');

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        $formEntity = $formManager->getById($formId);

        if (!$formEntity instanceof FormInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        $mailLayout = $formEntity->getMailLayout();

        if (!isset($mailLayout[$mailType])) {
            return $this->json(['success' => false, 'message' => sprintf('mailtype %s is not available', $mailType)]);
        }

        unset($mailLayout[$mailType]);

        $mailLayout = $this->cleanupMailLayout($mailLayout);

        $formEntity->setMailLayout(count($mailLayout) === 0 ? null : $mailLayout);
        $formEntity->setModificationDate(date('Y-m-d H:i:s'));

        try {
            $formEntity->save();
        } catch (\Exception $e) {
            $success = false;
            $message = sprintf('Error while saving form mail layout with id %d. Error was: %s', $formId, $e->getMessage());
        }

        return $this->json([
            'formId'  => (int) $formId,
            'success' => $success,
            'message' => $message
        ]);

    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function getMailEditorDataAction(Request $request)
    {
        $formId = $request->get('id');
        $mailType = $request->get('mailType');

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        /** @var Builder $backendFormBuilder */
        $backendFormBuilder = $this->get(Builder::class);

        $formEntity = $formManager->getById($formId);

        $fieldData = [];
        /** @var FormFieldInterface|FormFieldContainerInterface $field */
        foreach ($formEntity->getFields() as $field) {
            $fieldData[] = $field->toArray();
        }

        $formFields = $backendFormBuilder->generateExtJsFields($fieldData);

        $mailLayouts = $formEntity->getMailLayout();

        $widgets = $this->get(MailEditorWidgetRegistry::class)->getAll();

        $allWidgets = [];
        $widgetsConfiguration = [];

        foreach ($widgets as $widgetType => $widget) {
            $groupName = $widget->getWidgetGroupName();

            if (!isset($allWidgets[$groupName])) {
                $allWidgets[$groupName] = [
                    'label'    => $this->get('translator')->trans($groupName, [], 'admin'),
                    'elements' => []
                ];
            }

            if ($widget instanceof MailEditorFieldDataWidgetInterface) {
                foreach ($formFields as $field) {
                    $widgetFieldType = $widget->getWidgetIdentifierByField($widgetType, $field);
                    $widgetsConfiguration[$widgetFieldType] = $this->translateWidgetConfig($widget->getWidgetConfigByField($field));
                    $allWidgets[$groupName]['elements'][] = [
                        'type'             => $widgetType,
                        'subType'          => $widget->getSubTypeByField($field),
                        'label'            => $widget->getWidgetLabelByField($field),
                        'configIdentifier' => $widgetFieldType,
                    ];
                }
            } else {
                $widgetsConfiguration[$widgetType] = $this->translateWidgetConfig($widget->getWidgetConfig());
                $allWidgets[$groupName]['elements'][] = [
                    'type'             => $widgetType,
                    'subType'          => null,
                    'label'            => $this->get('translator')->trans($widget->getWidgetLabel(), [], 'admin'),
                    'configIdentifier' => $widgetType,
                ];
            }
        }

        $allWidgets = array_values($allWidgets);

        return $this->json([
            'formId'        => (int) $formId,
            'data'          => isset($mailLayouts[$mailType]) ? $mailLayouts[$mailType] : null,
            'configuration' => [
                'help'                => '',
                'widgetGroups'        => $allWidgets,
                'widgetConfiguration' => $widgetsConfiguration
            ]
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function saveFormMailEditorDataAction(Request $request)
    {
        $success = true;
        $message = '';

        $formId = $request->get('id');
        $mailType = $request->get('mailType');

        $mailLayouts = json_decode($request->get('data'), true);

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        $formEntity = $formManager->getById($formId);
        $storedMailLayout = is_array($formEntity->getMailLayout()) ? $formEntity->getMailLayout() : [];

        foreach ($mailLayouts as $locale => $mailLayout) {
            $mailLayout = str_replace('&nbsp;', ' ', $mailLayout);
            $mailLayout = preg_replace('/\s+/', ' ', $mailLayout);
            $storedMailLayout[$mailType][$locale] = $mailLayout;
        }

        $storedMailLayout = $this->cleanupMailLayout($storedMailLayout);

        $formEntity->setMailLayout(count($storedMailLayout) === 0 ? null : $storedMailLayout);
        $formEntity->setModificationDate(date('Y-m-d H:i:s'));

        try {
            $formEntity->save();
        } catch (\Exception $e) {
            $success = false;
            $message = sprintf('Error while saving form mail layout with id %d. Error was: %s', $formId, $e->getMessage());
        }

        return $this->json([
            'formId'  => (int) $formId,
            'success' => $success,
            'message' => $message
        ]);
    }

    /**
     * @param array $mailLayout
     *
     * @return array
     */
    protected function cleanupMailLayout(array $mailLayout)
    {
        foreach ($mailLayout as $mailType => $layout) {

            $mailLayout[$mailType] = array_filter($layout, function ($localizedLayout) {
                return !empty($localizedLayout);
            });

            if (count($mailLayout[$mailType]) === 0) {
                unset($mailLayout[$mailType]);
            }
        }

        return $mailLayout;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function translateWidgetConfig(array $config)
    {
        foreach ($config as $index => $element) {
            $config[$index]['label'] = $this->get('translator')->trans($element['label'], [], 'admin');
        }

        return $config;
    }
}
