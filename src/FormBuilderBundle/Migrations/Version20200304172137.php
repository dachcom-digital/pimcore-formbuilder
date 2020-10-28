<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Schema\Schema;
use FormBuilderBundle\Tool\Install;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20200304172137 extends AbstractPimcoreMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     *
     * @throws AbortMigrationException
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE formbuilder_forms ENGINE=InnoDB;');
        $this->addSql('CREATE TABLE formbuilder_output_workflow_channel (id INT AUTO_INCREMENT NOT NULL, output_workflow INT DEFAULT NULL, type VARCHAR(190) NOT NULL, configuration LONGTEXT DEFAULT NULL COMMENT "(DC2Type:object)", INDEX IDX_CEC462362C75DDDC (output_workflow), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB ROW_FORMAT=DYNAMIC;');
        $this->addSql('CREATE TABLE formbuilder_output_workflow (id INT AUTO_INCREMENT NOT NULL, form_definition INT DEFAULT NULL, `name` VARCHAR(255) DEFAULT NULL, success_management LONGTEXT DEFAULT NULL COMMENT "(DC2Type:object)", INDEX IDX_BCB7909761F7634C (form_definition), UNIQUE INDEX name_form (name, form_definition), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB ROW_FORMAT=DYNAMIC;');
        $this->addSql('ALTER TABLE formbuilder_output_workflow_channel ADD CONSTRAINT FK_CEC462362C75DDDC FOREIGN KEY (output_workflow) REFERENCES formbuilder_output_workflow (id);');
        $this->addSql('ALTER TABLE formbuilder_output_workflow ADD CONSTRAINT FK_BCB7909761F7634C FOREIGN KEY (form_definition) REFERENCES formbuilder_forms (id);');
        $this->addSql('ALTER TABLE formbuilder_forms CHANGE mailLayout mailLayout LONGTEXT DEFAULT NULL COMMENT "(DC2Type:object)";');
        $this->addSql('ALTER TABLE formbuilder_forms DROP INDEX `name`;');
        $this->addSql('ALTER TABLE formbuilder_forms ADD CONSTRAINT UNIQ_29DA5346999517A UNIQUE (`name`);');

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
