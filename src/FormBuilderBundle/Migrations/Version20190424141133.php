<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

class Version20190424141133 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     *
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $formBuilderTable = $schema->getTable('formbuilder_forms');

        if (!$formBuilderTable->hasColumn('mailLayout')) {
            $formBuilderTable->addColumn('mailLayout', 'text', ['default' => null, 'notnull' => false]);
        }

        \Pimcore\Cache::clearAll();

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
