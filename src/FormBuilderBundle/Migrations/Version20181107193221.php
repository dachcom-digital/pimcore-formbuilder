<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

class Version20181107193221 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     *
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $formBuilderTable = $schema->getTable('formbuilder_forms');

        if (!$formBuilderTable->hasColumn('creationDate')) {
            $formBuilderTable->addColumn('creationDate', 'datetime');
        }

        if (!$formBuilderTable->hasColumn('creationDate')) {
            $formBuilderTable->addColumn('creationDate', 'datetime');
        }

        if (!$formBuilderTable->hasColumn('modificationDate')) {
            $formBuilderTable->addColumn('modificationDate', 'datetime');
        }

        if (!$formBuilderTable->hasColumn('createdBy')) {
            $formBuilderTable->addColumn('createdBy', 'integer', ['length' => 11]);
        }

        if (!$formBuilderTable->hasColumn('modifiedBy')) {
            $formBuilderTable->addColumn('modifiedBy', 'integer', ['length' => 11]);
        }

        if ($formBuilderTable->hasColumn('date')) {
            $formBuilderTable->dropColumn('date');
        }

        \Pimcore\Cache::clearAll();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
