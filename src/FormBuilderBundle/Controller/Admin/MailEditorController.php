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
use Symfony\Component\Translation\TranslatorInterface;

class MailEditorController extends AdminController
{
    /**
     * @var MailEditorWidgetRegistry
     */
    protected $mailEditorWidgetRegistry;

    /**
     * @var FormDefinitionManager
     */
    protected $formDefinitionManager;

    /**
     * @var ExtJsFormBuilder
     */
    protected $extJsFormBuilder;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param MailEditorWidgetRegistry $mailEditorWidgetRegistry
     * @param FormDefinitionManager    $formDefinitionManager
     * @param ExtJsFormBuilder         $extJsFormBuilder
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        MailEditorWidgetRegistry $mailEditorWidgetRegistry,
        FormDefinitionManager $formDefinitionManager,
        ExtJsFormBuilder $extJsFormBuilder,
        TranslatorInterface $translator
    ) {
        $this->mailEditorWidgetRegistry = $mailEditorWidgetRegistry;
        $this->formDefinitionManager = $formDefinitionManager;
        $this->extJsFormBuilder = $extJsFormBuilder;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getMailEditorAvailableMailTypesAction(Request $request)
    {
        $formId = $request->get('id');

        $formDefinition = $this->formDefinitionManager->getById($formId);

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

        $formDefinition = $this->formDefinitionManager->getById($formId);

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
            $this->formDefinitionManager->saveRawEntity($formDefinition);
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
        $externalData = $request->get('externalData', 'false');

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

        if ($externalData === 'false') {
            $mailLayouts = $formDefinition->getMailLayout();
            $data['data'] = isset($mailLayouts[$mailType]) ? $mailLayouts[$mailType] : null;
        }

        return $this->json($data);
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

        $formDefinition = $this->formDefinitionManager->getById($formId);

        if ($formDefinition->hasOutputWorkflows()) {
            return $this->json([
                'formId'  => (int) $formId,
                'success' => false,
                'message' => 'You cannot use the global mail editor because this form already has some configured output workflows.'
            ]);
        }

        $storedMailLayout = is_array($formDefinition->getMailLayout()) ? $formDefinition->getMailLayout() : [];

        foreach ($mailLayouts as $locale => $mailLayout) {
            $mailLayout = str_replace('&nbsp;', ' ', $mailLayout);
            $mailLayout = preg_replace('/\s+/', ' ', $mailLayout);
            $storedMailLayout[$mailType][$locale] = $mailLayout;
        }

        $storedMailLayout = $this->cleanupMailLayout($storedMailLayout);

        $formDefinition->setMailLayout(count($storedMailLayout) === 0 ? null : $storedMailLayout);

        try {
            $this->formDefinitionManager->saveRawEntity($formDefinition);
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
            $config[$index]['label'] = $this->translator->trans($element['label'], [], 'admin');
        }

        return $config;
    }
}
