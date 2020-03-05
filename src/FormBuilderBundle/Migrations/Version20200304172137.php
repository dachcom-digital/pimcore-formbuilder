<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

class Version20200304172137 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE formbuilder_forms CHANGE mailLayout mailLayout LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\';');
        $this->addSql('ALTER TABLE formbuilder_forms RENAME INDEX name TO UNIQ_29DA5346999517A;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
