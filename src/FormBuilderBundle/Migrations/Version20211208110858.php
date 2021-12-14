<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormBuilderBundle\Tool\Install;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20211208110858 extends AbstractPimcoreMigration
{
    use ContainerAwareTrait;

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
        $installer = $this->container->get(Install::class);
        $installer->updateTranslations();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
