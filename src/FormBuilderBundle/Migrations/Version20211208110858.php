<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormBuilderBundle\Tool\Install;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20211208110858 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function doesSqlMigrations(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $installer = $this->container->get(Install::class);
        $installer->updateTranslations();
    }

    public function down(Schema $schema): void
    {
        // nothing to do
    }
}
