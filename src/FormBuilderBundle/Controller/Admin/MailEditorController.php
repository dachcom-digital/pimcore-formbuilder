<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Backend\Form\Builder;
use FormBuilderBundle\MailEditor\Widget\MailEditorFieldDataWidgetInterface;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MailEditorController extends AdminController
{
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

        $mailLayouts = $formEntity->getMailLayout();

        return $this->json([
            'formId'        => (int) $formId,
            'data'          => $mailLayouts,
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
        $mailLayouts = json_decode($request->get('data'), true);

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        $formEntity = $formManager->getById($formId);

        $storedMailLayout = is_array($formEntity->getMailLayout()) ? $formEntity->getMailLayout() : [];
        $normalizedMailLayouts = $storedMailLayout;

        foreach ($mailLayouts as $locale => $mailLayout) {
            $mailLayout = str_replace('&nbsp;', ' ', $mailLayout);
            $mailLayout = preg_replace('/\s+/', ' ', $mailLayout);

            if (empty($mailLayout) && isset($normalizedMailLayouts[$locale])) {
                unset($normalizedMailLayouts[$locale]);
                continue;
            }

            $normalizedMailLayouts[$locale] = $mailLayout;
        }

        $formEntity->setMailLayout(count($normalizedMailLayouts) === 0 ? null : $normalizedMailLayouts);
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

    protected function translateWidgetConfig(array $config)
    {
        foreach ($config as $index => $element) {
            $config[$index]['label'] = $this->get('translator')->trans($element['label'], [], 'admin');
        }

        return $config;
    }
}
