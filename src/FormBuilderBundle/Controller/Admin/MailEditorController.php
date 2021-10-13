<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\MailEditor\Widget\MailEditorFieldDataWidgetInterface;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;
use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MailEditorController extends AdminController
{
    protected MailEditorWidgetRegistry $mailEditorWidgetRegistry;
    protected FormDefinitionManager $formDefinitionManager;
    protected ExtJsFormBuilder $extJsFormBuilder;
    protected Translator $translator;

    public function __construct(
        MailEditorWidgetRegistry $mailEditorWidgetRegistry,
        FormDefinitionManager $formDefinitionManager,
        ExtJsFormBuilder $extJsFormBuilder,
        Translator $translator
    ) {
        $this->mailEditorWidgetRegistry = $mailEditorWidgetRegistry;
        $this->formDefinitionManager = $formDefinitionManager;
        $this->extJsFormBuilder = $extJsFormBuilder;
        $this->translator = $translator;
    }

    public function getMailEditorDataAction(Request $request): JsonResponse
    {
        $formId = $request->get('id');

        $formDefinition = $this->formDefinitionManager->getById($formId);

        $fieldData = [];
        foreach ($formDefinition->getFields() as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $fieldData[] = $field->toArray();
            }
        }

        $formFields = $this->extJsFormBuilder->generateExtJsFields($fieldData);

        $widgets = $this->mailEditorWidgetRegistry->getAll();

        $allWidgets = [];
        $widgetsConfiguration = [];

        foreach ($widgets as $widgetType => $widget) {
            $groupName = $widget->getWidgetGroupName();

            if (!isset($allWidgets[$groupName])) {
                $allWidgets[$groupName] = [
                    'label'    => $this->translator->trans($groupName, [], 'admin'),
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
                    'label'            => $this->translator->trans($widget->getWidgetLabel(), [], 'admin'),
                    'configIdentifier' => $widgetType,
                ];
            }
        }

        $allWidgets = array_values($allWidgets);

        $data = [
            'formId'        => (int) $formId,
            'configuration' => [
                'help'                => '',
                'widgetGroups'        => $allWidgets,
                'widgetConfiguration' => $widgetsConfiguration
            ]
        ];

        return $this->json($data);
    }

    protected function cleanupMailLayout(array $mailLayout): array
    {
        foreach ($mailLayout as $mailType => $layout) {
            $mailLayout[$mailType] = array_filter($layout, static function ($localizedLayout) {
                return !empty($localizedLayout);
            });

            if (count($mailLayout[$mailType]) === 0) {
                unset($mailLayout[$mailType]);
            }
        }

        return $mailLayout;
    }

    protected function translateWidgetConfig(array $config): array
    {
        foreach ($config as $index => $element) {
            $config[$index]['label'] = $this->translator->trans($element['label'], [], 'admin');
        }

        return $config;
    }
}
