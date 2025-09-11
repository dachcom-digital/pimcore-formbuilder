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

        // 1. Migrate formbuilder_forms table
        $this->migrateTable($connection, 'formbuilder_forms', ['configuration', 'conditionalLogic']);

        // 2. Migrate formbuilder_output_workflow table
        $this->migrateTable($connection, 'formbuilder_output_workflow', ['success_management']);

        // 3. Migrate formbuilder_output_workflow_channel table
        $this->migrateTable($connection, 'formbuilder_output_workflow_channel', ['configuration', 'funnel_actions']);

        // Change column types to JSON
        $this->addSql('ALTER TABLE formbuilder_forms MODIFY COLUMN configuration JSON');
        $this->addSql('ALTER TABLE formbuilder_forms MODIFY COLUMN conditionalLogic JSON');
        $this->addSql('ALTER TABLE formbuilder_output_workflow MODIFY COLUMN success_management JSON');
        $this->addSql('ALTER TABLE formbuilder_output_workflow_channel MODIFY COLUMN configuration JSON');
        $this->addSql('ALTER TABLE formbuilder_output_workflow_channel MODIFY COLUMN funnel_actions JSON');
    }

    private function migrateTable($connection, $tableName, $columns): void
    {
        $columnList = implode(', ', $columns);
        $conditions = array_map(fn($col) => "$col IS NOT NULL", $columns);
        $whereClause = implode(' OR ', $conditions);

        $result = $connection->executeQuery("SELECT id, $columnList FROM $tableName WHERE $whereClause");

        while ($row = $result->fetchAssociative()) {
            $updates = [];
            $values = [];

            foreach ($columns as $column) {
                if (!empty($row[$column])) {
                    $unserialized = @unserialize($row[$column]);
                    if ($unserialized !== false) {
                        $updates[] = "$column = ?";
                        $values[] = json_encode($unserialized);
                    } else {
                        // If unserialization fails, store as JSON string
                        $updates[] = "$column = ?";
                        $values[] = json_encode($row[$column]);
                    }
                } else {
                    $updates[] = "$column = ?";
                    $values[] = null;
                }
            }

            if (!empty($updates)) {
                $values[] = $row['id'];
                $connection->executeStatement(
                    "UPDATE $tableName SET " . implode(', ', $updates) . ' WHERE id = ?',
                    $values
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // This migration is not reversible as we're converting serialized data to JSON
        $this->throwIrreversibleMigrationException();
    }
}
