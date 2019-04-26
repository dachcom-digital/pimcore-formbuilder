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
                    $allWidgets[$groupName]['elements'][] = [
                        'type'       => $widgetType,
                        'identifier' => $widget->getWidgetIdentifierByField($field),
                        'label'      => $widget->getWidgetLabelByField($field),
                        'config'     => $widget->getWidgetConfigByField($field),
                    ];
                }
            } else {
                $allWidgets[$groupName]['elements'][] = [
                    'type'       => $widgetType,
                    'identifier' => $widgetType,
                    'label'      => $widget->getWidgetLabel(),
                    'config'     => $widget->getWidgetConfig(),
                ];
            }
        }

        $allWidgets = array_values($allWidgets);

        return $this->json([
            'formId'        => (int) $formId,
            'data'          => $formEntity->getMailLayout(),
            'configuration' => [
                'help'         => '',
                'widgetGroups' => $allWidgets,
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
        $mailLayout = $request->get('data');

        /** @var FormManager $formManager */
        $formManager = $this->get(FormManager::class);

        $formEntity = $formManager->getById($formId);

        $mailLayout = str_replace('&nbsp;', ' ', $mailLayout);
        $mailLayout = preg_replace('/\s+/', ' ', $mailLayout);

        $formEntity->setMailLayout(empty($mailLayout) ? null : $mailLayout);
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
}
