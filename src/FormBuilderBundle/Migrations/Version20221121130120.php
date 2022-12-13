<?php declare(strict_types=1);

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormBuilderBundle\Tool\Install;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20221121130120 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $installer = $this->container->get(Install::class);
        $installer->updateTranslations();

        if ($schema->hasTable('formbuilder_output_workflow')) {
            $table = $schema->getTable('formbuilder_output_workflow');
            if (!$table->hasColumn('funnel_workflow')) {
                $this->addSql('ALTER TABLE `formbuilder_output_workflow` ADD `funnel_workflow` TINYINT(1) NOT NULL;');
            }
        }

        if ($schema->hasTable('formbuilder_output_workflow_channel')) {

            $table = $schema->getTable('formbuilder_output_workflow_channel');

            if (!$table->hasColumn('funnel_actions')) {
                $this->addSql('ALTER TABLE `formbuilder_output_workflow_channel` ADD `funnel_actions` LONGTEXT DEFAULT NULL COMMENT "(DC2Type:object)";');
            }

            if (!$table->hasColumn('name')) {
                $this->addSql('ALTER TABLE `formbuilder_output_workflow_channel` ADD `name` VARCHAR(190) NOT NULL;');
                $this->addSql('UPDATE `formbuilder_output_workflow_channel` SET `name` = (SELECT UUID());');
                $this->addSql('CREATE UNIQUE INDEX `ow_name` ON `formbuilder_output_workflow_channel` (`output_workflow`, `name`);');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // nothing to do
    }
}
