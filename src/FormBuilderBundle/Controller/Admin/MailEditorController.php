<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\MailEditor\Widget\MailEditorFieldDataWidgetInterface;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;
use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
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

        /** @var FormDefinitionManager $formDefinitionManager */
        $formDefinitionManager = $this->get(FormDefinitionManager::class);

        $formDefinition = $formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        $mailLayouts = $formDefinition->getMailLayout();

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

        /** @var FormDefinitionManager $formDefinitionManager */
        $formDefinitionManager = $this->get(FormDefinitionManager::class);

        $formDefinition = $formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json(['success' => false, 'message' => 'form is not available']);
        }

        $mailLayout = $formDefinition->getMailLayout();

        if (!isset($mailLayout[$mailType])) {
            return $this->json(['success' => false, 'message' => sprintf('mailtype %s is not available', $mailType)]);
        }

        unset($mailLayout[$mailType]);

        $mailLayout = $this->cleanupMailLayout($mailLayout);

        $formDefinition->setMailLayout(count($mailLayout) === 0 ? null : $mailLayout);

        try {
            $formDefinitionManager->saveRawEntity($formDefinition);
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

        /** @var FormDefinitionManager $formDefinitionManager */
        $formDefinitionManager = $this->get(FormDefinitionManager::class);

        /** @var ExtJsFormBuilder $backendFormBuilder */
        $backendFormBuilder = $this->get(ExtJsFormBuilder::class);

        $formDefinition = $formDefinitionManager->getById($formId);

        $fieldData = [];
        foreach ($formDefinition->getFields() as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $fieldData[] = $field->toArray();
            }
        }

        $formFields = $backendFormBuilder->generateExtJsFields($fieldData);

        $mailLayouts = $formDefinition->getMailLayout();

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

        /** @var FormDefinitionManager $formDefinitionManager */
        $formDefinitionManager = $this->get(FormDefinitionManager::class);

        $formDefinition = $formDefinitionManager->getById($formId);
        $storedMailLayout = is_array($formDefinition->getMailLayout()) ? $formDefinition->getMailLayout() : [];

        foreach ($mailLayouts as $locale => $mailLayout) {
            $mailLayout = str_replace('&nbsp;', ' ', $mailLayout);
            $mailLayout = preg_replace('/\s+/', ' ', $mailLayout);
            $storedMailLayout[$mailType][$locale] = $mailLayout;
        }

        $storedMailLayout = $this->cleanupMailLayout($storedMailLayout);

        $formDefinition->setMailLayout(count($storedMailLayout) === 0 ? null : $storedMailLayout);

        try {
            $formDefinitionManager->saveRawEntity($formDefinition);
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
