<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\Builder\ExtJsFormBuilder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\OutputWorkflowType;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\OutputWorkflowManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OutputWorkflowController extends AdminController
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormDefinitionManager
     */
    protected $formDefinitionManager;

    /**
     * @var OutputWorkflowManager
     */
    protected $outputWorkflowManager;

    /**
     * @var ExtJsFormBuilder
     */
    protected $extJsFormBuilder;

    /**
     * @param Configuration         $configuration
     * @param FormFactoryInterface  $formFactory
     * @param FormDefinitionManager $formDefinitionManager
     * @param OutputWorkflowManager $outputWorkflowManager
     * @param ExtJsFormBuilder      $extJsFormBuilder
     */
    public function __construct(
        Configuration $configuration,
        FormFactoryInterface $formFactory,
        FormDefinitionManager $formDefinitionManager,
        OutputWorkflowManager $outputWorkflowManager,
        ExtJsFormBuilder $extJsFormBuilder
    ) {
        $this->configuration = $configuration;
        $this->formFactory = $formFactory;
        $this->formDefinitionManager = $formDefinitionManager;
        $this->outputWorkflowManager = $outputWorkflowManager;
        $this->extJsFormBuilder = $extJsFormBuilder;
    }

    /**
     * @param Request $request
     * @param int     $formId
     *
     * @return JsonResponse
     */
    public function getOutputWorkflowTreeAction(Request $request, int $formId)
    {
        $mainItems = [];

        $formDefinition = $this->formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json($mainItems);
        }

        if (!$formDefinition->hasOutputWorkflows()) {
            return $this->json($mainItems);
        }

        foreach ($formDefinition->getOutputWorkflows() as $outputWorkflow) {
            $mainItems[] = [
                'id'            => $outputWorkflow->getId(),
                'text'          => $outputWorkflow->getName(),
                'icon'          => '',
                'leaf'          => true,
                'iconCls'       => 'pimcore_icon_output_workflow',
                'allowChildren' => false
            ];
        }

        return $this->adminJson($mainItems);
    }

    /**
     * @param Request $request
     * @param int     $formId
     *
     * @return JsonResponse
     */
    public function getOutputWorkflowListAction(Request $request, int $formId)
    {
        $formDefinition = $this->formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return $this->json(['success' => true, 'outputWorkflows' => []]);
        }

        if (!$formDefinition->hasOutputWorkflows()) {
            return $this->json(['success' => true, 'outputWorkflows' => []]);
        }

        $outputWorkflows = [];
        foreach ($formDefinition->getOutputWorkflows() as $outputWorkflow) {
            $outputWorkflows[] = [
                'id'   => $outputWorkflow->getId(),
                'name' => $outputWorkflow->getName()
            ];
        }

        return $this->adminJson([
            'success'         => true,
            'outputWorkflows' => $outputWorkflows
        ]);
    }

    /**
     * @param Request $request
     * @param int     $outputWorkflowId
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function getOutputWorkflowDataAction(Request $request, int $outputWorkflowId)
    {
        $data = [
            'success' => true,
            'message' => null
        ];

        try {
            $outputWorkflow = $this->outputWorkflowManager->getById($outputWorkflowId);
            if ($outputWorkflow instanceof OutputWorkflowInterface) {
                $data['data'] = $this->extJsFormBuilder->generateExtJsOutputWorkflowForm($outputWorkflow);
            } else {
                throw new \Exception(sprintf('No output workflow for id %d found.', $outputWorkflowId));
            }
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => $e->getMessage() . ' (' . $e->getFile() . ': ' . $e->getLine() . ')'
            ];
        }

        return $this->adminJson($data);
    }

    /**
     * @param Request $request
     * @param int     $formId
     *
     * @return JsonResponse
     */
    public function addOutputWorkflowAction(Request $request, int $formId)
    {
        $name = $request->request->get('outputWorkflowName');

        $success = true;
        $message = null;
        $id = null;

        $formDefinition = $this->formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            $success = false;
            $message = sprintf('No form for ID "%s" found.', $name);
        } elseif ($this->outputWorkflowManager->getFormOutputWorkflowByName($name, $formId) instanceof OutputWorkflowInterface) {
            $success = false;
            $message = sprintf('Output Workflow with name "%s" already exists!', $name);
        } else {
            try {
                $outputWorkflow = $this->outputWorkflowManager->save(['formDefinition' => $formDefinition, 'name' => $name]);
                $id = $outputWorkflow->getId();
            } catch (\Exception $e) {
                $success = false;
                $message = sprintf('Error while creating new output workflow with name "%s". Error was: %s', $name, $e->getMessage());
            }
        }

        return $this->adminJson([
            'success' => $success,
            'message' => $message,
            'id'      => $id,
        ]);
    }

    /**
     * @param Request $request
     * @param int     $outputWorkflowId
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function saveOutputWorkflowAction(Request $request, int $outputWorkflowId)
    {
        $errors = [];
        $success = true;
        $message = null;

        $data = json_decode($request->request->get('data'), true);

        $outputWorkflow = $this->outputWorkflowManager->getById($outputWorkflowId);
        $storedOutputWorkflowName = $outputWorkflow->getName();

        $outputWorkflowName = $data['name'];

        $existingWorkflow = null;
        if ($outputWorkflowName !== $storedOutputWorkflowName) {
            try {
                $existingWorkflow = $this->outputWorkflowManager->getFormOutputWorkflowByName($outputWorkflowName, $outputWorkflow->getFormDefinition()->getId());
            } catch (\Exception $e) {
                // fail silently.
            }

            if ($existingWorkflow instanceof OutputWorkflowInterface) {
                return $this->json([
                    'success' => false,
                    'message' => sprintf('Output Workflow with name "%s" already exists in this form!', $outputWorkflowName)
                ]);
            }
        }

        $form = $this->formFactory->createNamed('', OutputWorkflowType::class, $outputWorkflow);

        $form->submit($data);

        if ($form->isValid()) {
            $this->outputWorkflowManager->saveRawEntity($outputWorkflow);
        } else {
            /** @var FormError $e */
            foreach ($form->getErrors(true, true) as $e) {
                $errorMessageTemplate = $e->getMessageTemplate();
                foreach ($e->getMessageParameters() as $key => $value) {
                    $errorMessageTemplate = str_replace($key, $value, $errorMessageTemplate);
                }
                $errors[] = sprintf('%s: %s', $e->getOrigin()->getConfig()->getName(), $errorMessageTemplate);
            }

            $success = false;
            $message = join('<br>', $errors);
        }

        return $this->adminJson([
            'success' => $success,
            'message' => $message,
            'id'      => $outputWorkflowId,
        ]);
    }

    /**
     * @param Request $request
     * @param int     $outputWorkflowId
     *
     * @return JsonResponse
     */
    public function deleteOutputWorkflowAction(Request $request, int $outputWorkflowId)
    {
        $success = true;
        $message = null;

        try {
            $this->outputWorkflowManager->delete($outputWorkflowId);
        } catch (\Exception $e) {
            $success = false;
            $message = sprintf('Error while deleting output workflow with id %d. Error was: %s', $outputWorkflowId, $e->getMessage());
        }

        return $this->adminJson([
            'success' => $success,
            'message' => $message,
            'id'      => $outputWorkflowId,
        ]);
    }
}
