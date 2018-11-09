<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;

class Version00000001 extends AbstractPimcoreMigration
{
    /**
     * @return bool
     */
    public function doesSqlMigrations(): bool
    {
        return false;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this is the installation version and has no further meaning.
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this is the installation version and has no further meaning.
    }
}
