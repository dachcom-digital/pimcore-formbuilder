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

namespace FormBuilderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use FormBuilderBundle\Factory\OutputWorkflowFactoryInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Repository\OutputWorkflowRepositoryInterface;

class OutputWorkflowManager
{
    public function __construct(
        protected OutputWorkflowFactoryInterface $outputWorkflowFactory,
        protected OutputWorkflowRepositoryInterface $outputWorkflowRepository,
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function getById(int $id): ?OutputWorkflowInterface
    {
        return $this->outputWorkflowRepository->findById($id);
    }

    /**
     * @throws \Exception
     */
    public function save(array $data, ?int $id = null): ?OutputWorkflowInterface
    {
        if (!is_null($id)) {
            $outputWorkflow = $this->outputWorkflowRepository->findById($id);
        } else {
            $outputWorkflow = $this->outputWorkflowFactory->createOutputWorkflow();
        }

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            return null;
        }

        if (array_key_exists('name', $data)) {
            $outputWorkflow->setName($data['name']);
        }

        if (array_key_exists('formDefinition', $data)) {
            $outputWorkflow->setFormDefinition($data['formDefinition']);
        }

        if (array_key_exists('funnelAware', $data)) {
            $outputWorkflow->setFunnelWorkflow($data['funnelAware'] === true);
        }

        $this->entityManager->persist($outputWorkflow);
        $this->entityManager->flush();

        return $outputWorkflow;
    }

    /**
     * @throws \Exception
     */
    public function saveRawEntity(OutputWorkflowInterface $outputWorkflow): void
    {
        $this->entityManager->persist($outputWorkflow);
        $this->entityManager->flush();
    }

    public function delete(int $id): void
    {
        $outputWorkflow = $this->outputWorkflowRepository->findById($id);

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            return;
        }

        $this->entityManager->remove($outputWorkflow);
        $this->entityManager->flush();
    }

    public function getFormOutputWorkflowByName(string $name, int $formId): ?OutputWorkflowInterface
    {
        return $this->outputWorkflowRepository->findByNameAndFormId($name, $formId);
    }

    public function outputWorkflowIsRequiredByConditionalLogic(OutputWorkflowInterface $outputWorkflow): bool
    {
        $cl = $outputWorkflow->getFormDefinition()->getConditionalLogic();

        foreach ($cl as $block) {
            if (count(
                array_filter(
                    $block['condition'] ?? [],
                    static function (array $condition) use ($outputWorkflow) {
                        return $condition['type'] === 'outputWorkflow' && in_array($outputWorkflow->getName(), $condition['outputWorkflows'], true);
                    }
                )
            ) > 0) {
                return true;
            }

            if (count(
                array_filter(
                    $block['action'] ?? [],
                    static function (array $action) use ($outputWorkflow) {
                        return $action['type'] === 'switchOutputWorkflow' && $action['workflowName'] === $outputWorkflow->getName();
                    }
                )
            ) > 0) {
                return true;
            }
        }

        return false;
    }
}
