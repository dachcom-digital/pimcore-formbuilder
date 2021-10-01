<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Db\Connection;
use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\DocType;
use Pimcore\Model\Property;
use Pimcore\Model\Translation;
use Pimcore\Tool\Admin;
use Symfony\Component\Filesystem\Filesystem;
use Pimcore\Model\User\Permission;

class Install extends SettingsStoreAwareInstaller
{
    protected array $permissionsToInstall = [
        'formbuilder_permission_settings'
    ];

    public function install(): void
    {
        $this->setupPaths();
        $this->installDbStructure();
        $this->installPermissions();
        $this->installTranslations();
        $this->installFormDataFolder();
        $this->installProperties();
        $this->installDocumentTypes();

        parent::install();
    }

    protected function setupPaths(): void
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
     * @throws InstallationException
     */
    protected function installTranslations(): void
    {
        $csv = $this->getInstallSourcesPath() . '/translations/frontend.csv';
        $csvAdmin = $this->getInstallSourcesPath() . '/translations/admin.csv';

        try {
            Translation::importTranslationsFromFile($csv, Translation::DOMAIN_DEFAULT, true, Admin::getLanguages());
        } catch (\Exception $e) {
            throw new InstallationException(sprintf('Failed to install website translations. error was: "%s"', $e->getMessage()));
        }

        try {
            Translation::importTranslationsFromFile($csvAdmin, Translation::DOMAIN_DEFAULT, true, Admin::getLanguages());
        } catch (\Exception $e) {
            throw new InstallationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }
    }

    protected function installDbStructure(): void
    {
        /** @var Connection $db */
        $db = \Pimcore\Db::get();
        $db->query(file_get_contents($this->getInstallSourcesPath() . '/sql/install.sql'));
    }

    /**
     * @throws InstallationException|\Exception
     */
    protected function installPermissions(): void
    {
        foreach ($this->permissionsToInstall as $permission) {
            $definition = Permission\Definition::getByKey($permission);

            if ($definition) {
                continue;
            }

            try {
                Permission\Definition::create($permission);
            } catch (\Throwable $e) {
                throw new InstallationException(sprintf('Failed to create permission "%s"', $permission));
            }
        }
    }

    /**
     * @throws InstallationException
     */
    protected function installFormDataFolder(): void
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
            throw new InstallationException(sprintf('Failed to create form data folder. Error was: "%s"', $e->getMessage()));
        }
    }

    public function updateProperties(): void
    {
        $this->installProperties();
    }

    /**
     * @throws InstallationException
     */
    protected function installProperties(): void
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
                throw new InstallationException(sprintf('Failed to save property "%s". Error was: "%s"', $key, $e->getMessage()));
            }
        }
    }

    /**
     * @throws InstallationException
     */
    protected function installDocumentTypes(): void
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
                'controller' => 'FormBuilderBundle\Controller\EmailController::emailAction',
                'template'   => 'FormBuilderBundle:Email:email.html.twig',
                'type'       => 'email',
                'priority'   => 0
            ]);
            $type->getDao()->save();
        } catch (\Exception $e) {
            throw new InstallationException(sprintf('Failed to create document type "%s". Error was: "%s"', $elementName, $e->getMessage()));
        }
    }

    protected function getInstallSourcesPath(): string
    {
        return __DIR__ . '/../Resources/install';
    }
}
