<?php

namespace FormBuilderBundle\Tool;

use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\DocType;
use Pimcore\Model\Translation;
use Pimcore\Tool\Admin;
use Pimcore\Model\User\Permission;

class Install extends SettingsStoreAwareInstaller
{
    protected array $permissionsToInstall = [
        'formbuilder_permission_settings'
    ];

    public function install(): void
    {
        $this->installDbStructure();
        $this->installPermissions();
        $this->installTranslations();
        $this->installFormDataFolder();
        $this->installDocumentTypes();

        parent::install();
    }

    public function updateTranslations(): void
    {
        $this->installTranslations();
    }

    /**
     * @throws InstallationException
     */
    protected function installTranslations(): void
    {
        $csvWebsite = $this->getInstallSourcesPath() . '/translations/frontend.csv';
        $csvAdmin = $this->getInstallSourcesPath() . '/translations/admin.csv';

        try {
            Translation::importTranslationsFromFile($csvWebsite, Translation::DOMAIN_DEFAULT, false, Admin::getLanguages());
        } catch (\Exception $e) {
            throw new InstallationException(sprintf('Failed to install website translations. error was: "%s"', $e->getMessage()));
        }

        try {
            Translation::importTranslationsFromFile($csvAdmin, Translation::DOMAIN_ADMIN, false, Admin::getLanguages());
        } catch (\Exception $e) {
            throw new InstallationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }
    }

    protected function installDbStructure(): void
    {
        $db = \Pimcore\Db::get();
        $db->executeQuery(file_get_contents($this->getInstallSourcesPath() . '/sql/install.sql'));
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

    /**
     * @throws InstallationException
     */
    protected function installDocumentTypes(): void
    {
        // get list of types
        $list = new DocType\Listing();

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
                'template'   => '@FormBuilder/email/email.html.twig',
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
        return __DIR__ . '/../../install';
    }
}
