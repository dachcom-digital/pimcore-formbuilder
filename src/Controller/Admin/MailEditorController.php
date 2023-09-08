<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\MailEditor\Widget\MailEditorFieldDataWidgetInterface;
use FormBuilderBundle\MailEditor\Widget\MailEditorWidgetInterface;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;
use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
use FormBuilderBundle\MailEditor\TemplateGenerator;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailEditorController extends AdminAbstractController
{
    public function __construct(
        protected MailEditorWidgetRegistry $mailEditorWidgetRegistry,
        protected FormDefinitionManager $formDefinitionManager,
        protected ExtJsFormBuilder $extJsFormBuilder,
        protected TranslatorInterface $translator
    ) {
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
        $widgetFieldsTemplate = null;

        /**
         * @var string                    $widgetType
         * @var MailEditorWidgetInterface $widget
         */
        foreach ($widgets as $widgetType => $widget) {

            $groupName = $widget->getWidgetGroupName();

            if (!isset($allWidgets[$groupName])) {
                $allWidgets[$groupName] = [
                    'label'    => $this->translator->trans($groupName, [], 'admin'),
                    'elements' => []
                ];
            }

            if ($widget instanceof MailEditorFieldDataWidgetInterface) {

                $fieldConfigElements = [];
                foreach ($formFields as $field) {

                    $widgetFieldType = $widget->getWidgetIdentifierByField($widgetType, $field);
                    $widgetsConfiguration[$widgetFieldType] = $this->translateWidgetConfig($widget->getWidgetConfigByField($field));

                    $fieldConfig = [
                        'type'             => $widgetType,
                        'subType'          => $widget->getSubTypeByField($field),
                        'label'            => $widget->getWidgetLabelByField($field),
                        'field_type'       => $field['type'] ?? null,
                        'configIdentifier' => $widgetFieldType,
                    ];

                    $children = [];
                    if (array_key_exists('fields', $field)) {
                        foreach ($field['fields'] as $subField) {
                            $widgetFieldType = $widget->getWidgetIdentifierByField($widgetType, $subField);

                            if (!array_key_exists($widgetFieldType, $widgetsConfiguration)) {
                                $widgetsConfiguration[$widgetFieldType] = $this->translateWidgetConfig($widget->getWidgetConfigByField($subField));
                            }

                            $children[] = [
                                'type'             => $widgetType,
                                'subType'          => $widget->getSubTypeByField($subField),
                                'label'            => $widget->getWidgetLabelByField($subField),
                                'field_type'       => $field['type'] ?? null,
                                'configIdentifier' => $widgetFieldType,
                            ];
                        }
                    }

                    if (count($fieldConfig) > 0) {
                        $fieldConfig['children'] = $children;
                    }

                    $fieldConfigElements[] = $fieldConfig;
                }

                $allWidgets[$groupName]['elements'] = $fieldConfigElements;
                $widgetFieldsTemplate = (new TemplateGenerator())->generateWidgetFieldTemplate($fieldConfigElements);

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
                'help'                 => '',
                'widgetGroups'         => $allWidgets,
                'widgetConfiguration'  => $widgetsConfiguration,
                'widgetFieldsTemplate' => $widgetFieldsTemplate,
            ]
        ];

        return $this->json($data);
    }

    protected function translateWidgetConfig(array $config): array
    {
        foreach ($config as $index => $element) {
            $config[$index]['label'] = $this->translator->trans($element['label'], [], 'admin');
        }

        return $config;
    }
}
