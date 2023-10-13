<?php

declare(strict_types=1);

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230908101855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace all workflowId fields with workflowName in switchWorkflow action';
    }

    public function up(Schema $schema): void
    {
        $workflowIdMapData = $this->connection->fetchAllAssociative('SELECT id, name FROM formbuilder_output_workflow');

        $workflowIdMap = array_reduce($workflowIdMapData, static function ($carry, $data) {
            $carry[$data['id']] = $data['name'];
            return $carry;
        }, []);

        $forms = $this->connection->fetchAllAssociative("SELECT id, conditionalLogic FROM formbuilder_forms WHERE conditionalLogic LIKE '%switchOutputWorkflow%'");

        foreach ($forms as $form) {

            $conditionalLogic = array_map(static function ($logic) use ($workflowIdMap) {
                return [
                    'condition' => array_map(static function ($condition) use ($workflowIdMap) {
                        if ($condition['type'] !== 'outputWorkflow') {
                            return $condition;
                        }

                        foreach ($condition['outputWorkflows'] as $index => $outputWorkflowId) {
                            $condition['outputWorkflows'][$index] = $workflowIdMap[$outputWorkflowId];
                        }

                        return $condition;

                    }, $logic['condition']),
                    'action' => array_map(static function ($action) use ($workflowIdMap) {
                        if ($action['type'] !== 'switchOutputWorkflow') {
                            return $action;
                        }

                        $action['workflowName'] = $workflowIdMap[$action['workflowId']];
                        unset($action['workflowId']);

                        return $action;

                    }, $logic['action'])
                ];
            }, unserialize($form['conditionalLogic']));

            $this->addSql('UPDATE formbuilder_forms SET conditionalLogic = ? WHERE id = ?', [serialize($conditionalLogic), $form['id']]);
        }
    }

    public function down(Schema $schema): void
    {
        $workflowIdMapData = $this->connection->fetchAllAssociative('SELECT id, name FROM formbuilder_output_workflow');

        $workflowIdMap = array_reduce($workflowIdMapData, static function ($carry, $data) {
            $carry[$data['name']] = $data['id'];
            return $carry;
        }, []);

        $forms = $this->connection->fetchAllAssociative("SELECT id, conditionalLogic FROM formbuilder_forms WHERE conditionalLogic LIKE '%switchOutputWorkflow%'");

        foreach ($forms as $form) {
            $conditionalLogic = array_map(static function ($logic) use ($workflowIdMap) {
                return [
                    'condition' => array_map(static function ($condition) use ($workflowIdMap) {
                        if ($condition['type'] !== 'outputWorkflow') {
                            return $condition;
                        }

                        foreach ($condition['outputWorkflows'] as $index => $outputWorkflowId) {
                            $condition['outputWorkflows'][$index] = $workflowIdMap[$outputWorkflowId];
                        }

                        return $condition;

                    }, $logic['condition']),
                    'action'    => array_map(static function ($action) use ($workflowIdMap) {
                        if ($action['type'] !== 'switchOutputWorkflow') {
                            return $action;
                        }

                        $action['workflowId'] = $workflowIdMap[$action['workflowName']];
                        unset($action['workflowName']);

                        return $action;

                    }, $logic['action'])
                ];
            }, unserialize($form['conditionalLogic']));

            $this->addSql('UPDATE formbuilder_forms SET conditionalLogic = ? WHERE id = ?', [serialize($conditionalLogic), $form['id']]);
        }
    }
}
