<?php

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20200502174518 extends AbstractPimcoreMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `formbuilder_output_workflow` ROW_FORMAT=DYNAMIC');
        $this->addSql('ALTER TABLE `formbuilder_output_workflow_channel` ROW_FORMAT=DYNAMIC');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
