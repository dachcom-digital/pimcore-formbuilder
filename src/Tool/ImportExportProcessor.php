<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Tool;

use Doctrine\DBAL\Connection;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\OutputWorkflowManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflowChannel;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

class ImportExportProcessor
{
    public const FORM_SECTION_OUTPUT_WORKFLOWS = 'outputWorkflows';
    public const FORM_SECTION_CONDITIONAL_LOGIC = 'conditionalLogic';

    public function __construct(
        protected Connection $connection,
        protected FormDefinitionManager $formDefinitionManager,
        protected OutputWorkflowManager $outputWorkflowManager
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function processFormDefinitionToYaml(int $formId): string
    {
        $fQb = $this->connection->createQueryBuilder();
        $fQb
            ->select('*')
            ->from('formbuilder_forms')
            ->where('id = :id')
            ->setParameter('id', $formId);

        $rawFormDefinition = $fQb->execute()->fetchAssociative();

        if ($rawFormDefinition === false) {
            throw new NotFoundHttpException(sprintf('form with id %d not found', $formId));
        }

        $fowQb = $this->connection->createQueryBuilder();
        $fowQb
            ->select('*')
            ->from('formbuilder_output_workflow')
            ->where('form_definition = :id')
            ->setParameter('id', $formId);

        $outputWorkflows = [];
        foreach ($fowQb->executeQuery()->fetchAllAssociative() as $rawFormOutputWorkflowDefinition) {
            $fowChannelQb = $this->connection->createQueryBuilder();
            $fowChannelQb
                ->select('*')
                ->from('formbuilder_output_workflow_channel')
                ->where('output_workflow = :id')
                ->setParameter('id', $rawFormOutputWorkflowDefinition['id']);

            $channels = [];
            foreach ($fowChannelQb->executeQuery()->fetchAllAssociative() as $rawFormOutputWorkflowChannelDefinition) {
                $channels[] = [
                    'type'           => $rawFormOutputWorkflowChannelDefinition['type'],
                    'name'           => $rawFormOutputWorkflowChannelDefinition['name'],
                    'configuration'  => is_string($rawFormOutputWorkflowChannelDefinition['configuration'])
                        ? unserialize($rawFormOutputWorkflowChannelDefinition['configuration'], ['allowed_classes' => false])
                        : null,
                    'funnel_actions' => is_string($rawFormOutputWorkflowChannelDefinition['funnel_actions'])
                        ? unserialize($rawFormOutputWorkflowChannelDefinition['funnel_actions'], ['allowed_classes' => false])
                        : null,
                ];
            }

            $outputWorkflows[] = [
                'channels'           => $channels,
                'name'               => $rawFormOutputWorkflowDefinition['name'],
                'funnel_workflow'    => $rawFormOutputWorkflowDefinition['funnel_workflow'] === '1',
                'success_management' => is_string($rawFormOutputWorkflowDefinition['success_management'])
                    ? unserialize($rawFormOutputWorkflowDefinition['success_management'], ['allowed_classes' => false])
                    : null,
            ];
        }

        $data = [
            'configuration'     => is_string($rawFormDefinition['configuration'])
                ? unserialize($rawFormDefinition['configuration'], ['allowed_classes' => false])
                : null,
            'fields'            => $rawFormDefinition['fields']
                ? unserialize($rawFormDefinition['fields'], ['allowed_classes' => false])
                : null,
            'conditional_logic' => $rawFormDefinition['conditionalLogic']
                ? unserialize($rawFormDefinition['conditionalLogic'], ['allowed_classes' => false])
                : null,
            'output_workflows'  => $outputWorkflows,
        ];

        return Yaml::dump($data);
    }

    /**
     * @throws \Throwable
     */
    public function processYamlToFormDefinition(int $formId, mixed $data, array $importOptions): void
    {
        $formContent = Yaml::parse($data);

        if (!is_array($formContent) || count($formContent) === 0) {
            throw new \Exception('Invalid or empty import data');
        }

        $formDefinition = $this->formDefinitionManager->getById($formId);

        if (!$formDefinition instanceof FormDefinitionInterface) {
            throw new \Exception(sprintf('Form definition with id %d for mapping not found', $formId));
        }

        $data = [
            'form_name'   => $formDefinition->getName(),
            'form_config' => $formContent['configuration'] ?? [],
            'form_fields' => $formContent['fields'] ? ['fields' => $formContent['fields']] : [],
        ];

        if ($importOptions[self::FORM_SECTION_CONDITIONAL_LOGIC] === true) {
            $data['form_conditional_logic'] = $formContent['conditional_logic'] ?? [];
        }

        $this->formDefinitionManager->save($data, $formDefinition->getId());

        if ($importOptions[self::FORM_SECTION_OUTPUT_WORKFLOWS] === false) {
            return;
        }

        // remove all workflows and channels first (yes, we informed users about that earlier)
        /** @var OutputWorkflowInterface $outputWorkflow */
        foreach ($formDefinition->getOutputWorkflows() as $outputWorkflow) {
            $this->outputWorkflowManager->delete($outputWorkflow->getId());
        }

        if (is_array($formContent['output_workflows'])) {
            foreach ($formContent['output_workflows'] as $outputWorkflowDefinition) {
                /** @var OutputWorkflowInterface $outputWorkflow */
                $outputWorkflow = $this->outputWorkflowManager->save([
                    'name'           => $outputWorkflowDefinition['name'],
                    'funnelAware'    => $outputWorkflowDefinition['funnel_workflow'] ?? false,
                    'formDefinition' => $formDefinition
                ]);

                $outputWorkflow->setSuccessManagement($outputWorkflowDefinition['success_management']);

                if (is_array($outputWorkflowDefinition['channels'])) {
                    foreach ($outputWorkflowDefinition['channels'] as $channelDefinition) {
                        $channel = new OutputWorkflowChannel();
                        $channel->setType($channelDefinition['type']);
                        $channel->setConfiguration($channelDefinition['configuration']);
                        $channel->setName($channelDefinition['name'] ?? null);
                        $channel->setFunnelActions($channelDefinition['funnel_actions'] ?? null);
                        $channel->setOutputWorkflow($outputWorkflow);

                        $outputWorkflow->addChannel($channel);
                    }
                }

                $formDefinition->addOutputWorkflow($outputWorkflow);

                $this->outputWorkflowManager->saveRawEntity($outputWorkflow);
            }
        }
    }
}
