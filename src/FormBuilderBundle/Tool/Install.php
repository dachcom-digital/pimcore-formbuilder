<?php

namespace FormBuilderBundle\Tool;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\MigrationException;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Db\PimcoreExtensionsTrait;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;
use Pimcore\Migrations\Migration\InstallMigration;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\DocType;
use Pimcore\Model\Property;
use Pimcore\Model\Translation;
use Pimcore\Tool\Admin;
use Symfony\Component\Filesystem\Filesystem;
use Pimcore\Model\User\Permission;

class Install extends MigrationInstaller
{
    /**
     * @var array
     */
    protected $permissionsToInstall = [
        'formbuilder_permission_settings'
    ];

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion(): string
    {
        return '00000001';
    }

    /**
     * @throws AbortMigrationException
     * @throws MigrationException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function beforeInstallMigration()
    {
        $markVersionsAsMigrated = true;

        // legacy:
        //   we switched from config to migration
        //   if config.yml exists, this instance needs to migrate
        //   so every migration needs to run.
        // fresh:
        //   skip all versions since they are not required anymore
        //   (fresh installation does not require any version migrations)
        $fileSystem = new Filesystem();
        if ($fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH . '/config.yml')) {
            $markVersionsAsMigrated = false;
        }

        if ($markVersionsAsMigrated === true) {
            $migrationConfiguration = $this->migrationManager->getBundleConfiguration($this->bundle);
            $this->migrationManager->markVersionAsMigrated($migrationConfiguration->getVersion($migrationConfiguration->getLatestVersion()));
        }

        $this->initializeFreshSetup();
    }

    /**
     * @param Schema  $schema
     * @param Version $version
     */
    public function migrateInstall(Schema $schema, Version $version)
    {
        /** @var InstallMigration $migration */
        $migration = $version->getMigration();
        if ($migration->isDryRun()) {
            $this->outputWriter->write('<fg=cyan>DRY-RUN:</> Skipping installation');

            return;
        }
    }

    /**
     * @throws AbortMigrationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function initializeFreshSetup()
    {
        $this->setupPaths();
        $this->installDbStructure();
        $this->installPermissions();
        $this->installTranslations();
        $this->installFormDataFolder();
        $this->installProperties();
        $this->installDocumentTypes();
    }

    /**
     * @param Schema  $schema
     * @param Version $version
     */
    public function migrateUninstall(Schema $schema, Version $version)
    {
        /** @var InstallMigration $migration */
        $migration = $version->getMigration();
        if ($migration->isDryRun()) {
            $this->outputWriter->write('<fg=cyan>DRY-RUN:</> Skipping uninstallation');

            return;
        }

        // currently nothing to do.
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * @param string|null $version
     */
    protected function beforeUpdateMigration(string $version = null)
    {
        $this->setupPaths();
    }

    /**
     * install or update config file.
     */
    protected function setupPaths()
    {
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH)) {
            $fileSystem->mkdir(Configuration::SYSTEM_CONFIG_DIR_PATH);
        }

        if (!$fileSystem->exists(Configuration::STORE_PATH)) {
            $fileSystem->mkdir(Configuration::STORE_PATH, 0755);
        }
    }

    /**
     * Only an alias to allow migrations to execute.
     *
     * @throws AbortMigrationException
     */
    public function updateTranslations()
    {
        $this->installTranslations();
    }

    /**
     * @throws AbortMigrationException
     */
    protected function installTranslations()
    {
        $csv = $this->getInstallSourcesPath() . '/translations/frontend.csv';
        $csvAdmin = $this->getInstallSourcesPath() . '/translations/admin.csv';

        try {
            Translation\Website::importTranslationsFromFile($csv, true, Admin::getLanguages());
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }

        try {
            Translation\Admin::importTranslationsFromFile($csvAdmin, true, Admin::getLanguages());
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function installDbStructure()
    {
        /** @var PimcoreExtensionsTrait $db */
        $db = \Pimcore\Db::get();
        $db->query(file_get_contents($this->getInstallSourcesPath() . '/sql/install.sql'));
    }

    /**
     * @throws AbortMigrationException
     */
    protected function installPermissions()
    {
        foreach ($this->permissionsToInstall as $permission) {
            $definition = Permission\Definition::getByKey($permission);

            if ($definition) {
                $this->outputWriter->write(sprintf(
                    '     <comment>WARNING:</comment> Skipping permission "%s" as it already exists',
                    $permission
                ));

                continue;
            }

            try {
                Permission\Definition::create($permission);
            } catch (\Throwable $e) {
                throw new AbortMigrationException(sprintf('Failed to create permission "%s"', $permission));
            }
        }
    }

    /**
     * @throws AbortMigrationException
     */
    protected function installFormDataFolder()
    {
        //create folder for upload storage!
        $folderName = 'formdata';

        if (Asset\Folder::getByPath('/' . $folderName) instanceof Asset\Folder) {
            return;
        }

        $folder = new Asset\Folder();
        $folder->setCreationDate(time());
        $folder->setLocked(true);
        $folder->setUserOwner(1);
        $folder->setUserModification(0);
        $folder->setParentId(1);
        $folder->setFilename($folderName);

        try {
            $folder->save();
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to create form data folder. Error was: "%s"', $e->getMessage()));
        }
    }

    /**
     * Only an alias to allow migrations to execute.
     *
     * @throws AbortMigrationException
     */
    public function updateProperties()
    {
        $this->installProperties();
    }

    /**
     * @throws AbortMigrationException
     */
    protected function installProperties()
    {
        $properties = [];

        foreach ($properties as $key => $propertyConfig) {

            $defProperty = Property\Predefined::getByKey($key);
            if ($defProperty instanceof Property\Predefined) {
                continue;
            }

            $property = new Property\Predefined();
            $property->setKey($key);
            $property->setType($propertyConfig['type']);
            $property->setName($propertyConfig['name']);

            $property->setDescription($propertyConfig['description']);
            $property->setCtype($propertyConfig['ctype']);
            $property->setInheritable(false);

            try {
                $property->getDao()->save();
            } catch (\Exception $e) {
                throw new AbortMigrationException(sprintf('Failed to save property "%s". Error was: "%s"', $key, $e->getMessage()));
            }
        }
    }

    /**
     * @throws AbortMigrationException
     */
    protected function installDocumentTypes()
    {
        // get list of types
        $list = new DocType\Listing();
        $list->getDao()->load();

        $skipInstall = false;
        $elementName = 'Formbuilder Email';

        foreach ($list->getDocTypes() as $type) {
            if ($type->getName() === $elementName) {
                $skipInstall = true;

                break;
            }
        }

        if ($skipInstall === true) {
            return;
        }

        try {
            $type = DocType::create();
            $type->setValues([
                'name'       => $elementName,
                'module'     => 'FormBuilderBundle',
                'controller' => '@FormBuilderBundle\Controller\EmailController',
                'action'     => 'email',
                'template'   => 'FormBuilderBundle:Email:email.html.twig',
                'type'       => 'email',
                'priority'   => 0
            ]);
            $type->getDao()->save();
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to create document type "%s". Error was: "%s"', $elementName, $e->getMessage()));
        }
    }

    /**
     * @return string
     */
    protected function getInstallSourcesPath()
    {
        return __DIR__ . '/../Resources/install';
    }
}
