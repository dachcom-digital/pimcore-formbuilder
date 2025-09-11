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
        return 'Migrate formbuilder_forms.configuration and formbuilder_forms.conditionalLogic data from object to json format';
    }

    public function up(Schema $schema): void
    {
        // First migrate the data
        $this->addSql("
            UPDATE formbuilder_forms
            SET configuration = CASE
                WHEN configuration IS NOT NULL AND configuration != ''
                THEN JSON_QUOTE(configuration)
                ELSE NULL
            END,
            conditionalLogic = CASE
                WHEN conditionalLogic IS NOT NULL AND conditionalLogic != ''
                THEN JSON_QUOTE(conditionalLogic)
                ELSE NULL
            END;
        ");

        // Then change the column types
        $table = $schema->getTable('formbuilder_forms');

        if ($table->hasColumn('configuration')) {
            $table->changeColumn('configuration', [
                'type' => \Doctrine\DBAL\Types\Types::JSON,
                'notnull' => false,
                'comment' => null
            ]);
        }

        if ($table->hasColumn('conditionalLogic')) {
            $table->changeColumn('conditionalLogic', [
                'type' => \Doctrine\DBAL\Types\Types::JSON,
                'notnull' => false,
                'comment' => null
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        // This migration is not reversible as we're converting serialized data to JSON
        $this->throwIrreversibleMigrationException();
    }
}
