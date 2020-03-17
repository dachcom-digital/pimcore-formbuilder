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
        $this->addSql('ALTER TABLE formbuilder_forms ENGINE=InnoDB;');

        // @todo: add migration data!!
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
