<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20250911163105 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription(): string
    {
        return 'Migrate all formbuilder object type columns to json format';
    }

    public function up(Schema $schema): void
    {
        $connection = $this->connection;

        $this->migrateTable($connection, 'formbuilder_forms', ['configuration', 'conditionalLogic'], 'id');
        $this->migrateTable($connection, 'formbuilder_output_workflow', ['success_management'], 'id');
        $this->migrateTable($connection, 'formbuilder_output_workflow_channel', ['configuration', 'funnel_actions'], 'id');
        $this->migrateTable($connection, 'formbuilder_double_opt_in_session', ['additional_data'], 'token');

        // Change column types to JSON
        $this->addSql('ALTER TABLE formbuilder_forms MODIFY COLUMN configuration JSON');
        $this->addSql('ALTER TABLE formbuilder_forms MODIFY COLUMN conditionalLogic JSON');
        $this->addSql('ALTER TABLE formbuilder_output_workflow MODIFY COLUMN success_management JSON');
        $this->addSql('ALTER TABLE formbuilder_output_workflow_channel MODIFY COLUMN configuration JSON');
        $this->addSql('ALTER TABLE formbuilder_output_workflow_channel MODIFY COLUMN funnel_actions JSON');
        $this->addSql('ALTER TABLE formbuilder_double_opt_in_session MODIFY COLUMN additional_data JSON');
    }

    private function migrateTable($connection, $tableName, $columns, $primaryKey = 'id'): void
    {
        $columnList = implode(', ', $columns);
        $conditions = array_map(fn($col) => "$col IS NOT NULL", $columns);
        $whereClause = implode(' OR ', $conditions);

        $result = $connection->executeQuery("SELECT $primaryKey, $columnList FROM $tableName WHERE $whereClause");

        while ($row = $result->fetchAssociative()) {
            $updates = [];
            $values = [];

            foreach ($columns as $column) {
                if (!empty($row[$column])) {
                    $unserialized = @unserialize($row[$column]);
                    if ($unserialized !== false) {
                        $updates[] = "$column = ?";
                        $values[] = json_encode($unserialized);
                    } elseif ($row[$column] === '[]') {
                        $updates[] = "$column = ?";
                        $values[] = '[]';
                    } elseif ($row[$column] === '{}') {
                        $updates[] = "$column = ?";
                        $values[] = '{}';
                    }
                } else {
                    $updates[] = "$column = ?";
                    $values[] = null;
                }
            }

            if (!empty($updates)) {
                $values[] = $row[$primaryKey];
                $connection->executeStatement(
                    "UPDATE $tableName SET " . implode(', ', $updates) . " WHERE $primaryKey = ?",
                    $values
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // This migration is not reversible because we are converting serialized data to JSON
        $this->throwIrreversibleMigrationException();
    }
}
