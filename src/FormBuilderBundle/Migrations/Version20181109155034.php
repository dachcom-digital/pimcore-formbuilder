<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

class Version20181109155034 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     *
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $formBuilderTable = $schema->getTable('formbuilder_forms');

        if (!$formBuilderTable->hasColumn('group')) {
            $formBuilderTable->addColumn('group', 'string', ['length' => 255, 'default' => null, 'notnull' => false]);
        }

        \Pimcore\Cache::clearAll();

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // no down migration available.
    }
}
