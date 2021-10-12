<?php declare(strict_types=1);

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\MigrationNotExecuted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class Version20211011171530 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('formbuilder_forms')) {
            throw new MigrationNotExecuted('Table formbuilder_forms is missing');
        }

        $table = $schema->getTable('formbuilder_forms');

        if (!$table->hasColumn('configuration')) {
            $this->addSql('ALTER TABLE formbuilder_forms ADD configuration LONGTEXT DEFAULT NULL COMMENT "(DC2Type:object)"');
        }

        if (!$table->hasColumn('conditionalLogic')) {
            $this->addSql('ALTER TABLE formbuilder_forms ADD conditionalLogic LONGTEXT DEFAULT NULL COMMENT "(DC2Type:object)"');
        }

        if (!$table->hasColumn('configuration')) {
            $this->addSql('ALTER TABLE formbuilder_forms ADD fields LONGTEXT DEFAULT NULL COMMENT "(DC2Type:form_builder_fields)"');
        }

        $legacyFormStoragePath = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/forms';

        if (!is_dir($legacyFormStoragePath)) {
            return;
        }

        $finder = new Finder();
        $filesystem = new Filesystem();

        foreach ($finder->in($legacyFormStoragePath)->files()->name('*.yml') as $configFile) {

            $data = Yaml::parse($configFile->getContents());

            $fileNameData = explode('_', $configFile->getFilename());

            if (count($fileNameData) !== 2) {
                $this->write(sprintf('Cannot migrate "%s", form definition id not found', $configFile->getFilename()));
                continue;
            }

            $formDefinitionId = (int) $fileNameData[1];

            $this->write(sprintf('Migrating Form "%s"...', $configFile->getFilename()));

            $configuration = $data['config'] ?? [];
            $conditionalLogic = $data['conditional_logic'] ?? [];
            $fields = $data['fields'] ?? [];

            $this->addSql(sprintf('UPDATE formbuilder_forms SET `configuration` = "%s" WHERE `id` = %d', addslashes(serialize($configuration)), $formDefinitionId));
            $this->addSql(sprintf('UPDATE formbuilder_forms SET `conditionalLogic` = "%s" WHERE `id` = %d', addslashes(serialize($conditionalLogic)), $formDefinitionId));
            $this->addSql(sprintf('UPDATE formbuilder_forms SET `fields` = "%s" WHERE `id` = %d', addslashes(serialize($fields)), $formDefinitionId));

            // remove config file
            $filesystem->remove($configFile->getRealPath());
        }
    }

    public function down(Schema $schema): void
    {
        // disabled
    }
}
