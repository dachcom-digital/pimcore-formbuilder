<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181214160753 extends AbstractPimcoreMigration
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
        if ($fileSystem->exists(PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/import')) {
            $fileSystem->remove(PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/import');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
