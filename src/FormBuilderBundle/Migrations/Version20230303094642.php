<?php

declare(strict_types=1);

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormBuilderBundle\Tool\Install;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20230303094642 extends AbstractMigration
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
