<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Symfony\Component\Filesystem\Filesystem;

class Version20181107184706 extends AbstractPimcoreMigration
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
        $fileSystem = new Filesystem();
        if ($fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH . '/config.yml')) {
            $fileSystem->remove(Configuration::SYSTEM_CONFIG_DIR_PATH . '/config.yml');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // no down migration available.
    }
}
