<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\FormBuilderBundle;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Model\Document\DocType;
use Pimcore\Model\Property;
use Pimcore\Model\Tool\Setup;
use Pimcore\Model\Asset;
use Pimcore\Model\Translation;
use Pimcore\Tool\Admin;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class Install extends AbstractInstaller
{
    /**
     * @var string
     */
    private $installSourcesPath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * Install constructor.
     */
    public function __construct()
    {
        $this->installSourcesPath = __DIR__ . '/../Resources/install';
        $this->fileSystem = new Filesystem();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->installOrUpdateConfigFile();
        $this->injectDbData();
        $this->installTranslations();
        $this->installFormDataFolder();
        $this->installProperties();
        $this->installDocumentTypes();
    }

    /**
     * For now, just update the config file to the current version.
     * {@inheritdoc}
     */
    public function update()
    {
        $this->installTranslations();
        $this->installProperties();
        $this->installOrUpdateConfigFile();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        $target = PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/config.yml';

        if ($this->fileSystem->exists($target)) {
            $this->fileSystem->rename(
                $target,
                PIMCORE_PRIVATE_VAR . '/bundles/FormBuilderBundle/config_backup.yml'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeInstalled()
    {
        return !$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUninstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUpdated()
    {
        $needUpdate = false;
        if ($this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $config = Yaml::parse(file_get_contents(Configuration::SYSTEM_CONFIG_FILE_PATH));
            if ($config['version'] !== FormBuilderBundle::BUNDLE_VERSION) {
                $needUpdate = true;
            }
        }

        return $needUpdate;
    }

    /**
     * install or update config file
     */
    private function installOrUpdateConfigFile()
    {
        if (!$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH)) {
            $this->fileSystem->mkdir(Configuration::SYSTEM_CONFIG_DIR_PATH);
        }

        if (!$this->fileSystem->exists(Configuration::STORE_PATH)) {
            $this->fileSystem->mkdir(Configuration::STORE_PATH, 0755);
        }

        $config = ['version' => FormBuilderBundle::BUNDLE_VERSION];
        $yml = Yaml::dump($config);
        file_put_contents(Configuration::SYSTEM_CONFIG_FILE_PATH, $yml);
    }

    /**
     * @throws \Exception
     */
    private function installTranslations()
    {
        $csv = $this->installSourcesPath . '/translations/frontend.csv';
        $csvAdmin = $this->installSourcesPath . '/translations/admin.csv';

        Translation\Website::importTranslationsFromFile($csv, true, Admin::getLanguages());
        Translation\Admin::importTranslationsFromFile($csvAdmin, true, Admin::getLanguages());
    }

    /**
     *
     */
    public function injectDbData()
    {
        $setup = new Setup();
        $setup->insertDump($this->installSourcesPath . '/sql/install.sql');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function installFormDataFolder()
    {
        //create folder for upload storage!
        $folderName = 'formdata';

        if (!Asset\Folder::getByPath('/' . $folderName) instanceof Asset\Folder) {
            $folder = new Asset\Folder();
            $folder->setCreationDate(time());
            $folder->setLocked(true);
            $folder->setUserOwner(1);
            $folder->setUserModification(0);
            $folder->setParentId(1);
            $folder->setFilename($folderName);
            $folder->save();
        }

        return true;
    }

    /**
     * @return bool
     */
    private function installProperties()
    {
        $properties = [

            'mail_disable_default_mail_body'       => [
                'ctype'       => 'document',
                'type'        => 'bool',
                'name'        => 'Mail: Use custom template fields layout',
                'description' => 'If the mail_disable_default_mail_body property is defined and checked, you need to add your own data to the mail template. You can use all the field names as placeholder.'
            ],
            'mail_successfully_sent'               => [
                'ctype'       => 'document',
                'type'        => 'document',
                'name'        => 'Mail: Message after Submit',
                'description' => 'Use the mail_successfully_sent property to define a message after the form has been successfully sent. There are three options: "String", "Snippet", "Dokument"'

            ],
            'mail_successfully_sent_flash_message' => [
                'ctype'       => 'document',
                'type'        => 'text',
                'name'        => 'Mail: Flash Message after Success-Redirect',
                'description' => 'Define a flash message which should show up after a form has been successfully submitted. Note: This only works if "mail_successfully_sent" property is a document.'
            ],
            'mail_ignore_fields'                   => [
                'ctype'       => 'document',
                'type'        => 'text',
                'name'        => 'Mail: Ignored Fields in Email',
                'description' => 'In some cases, you don\'t want to send specific fields via mail. Add one or multiple (comma separated) fields as string.'
            ]
        ];

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
            $property->save();
        }

        return true;
    }

    /**
     * @return bool
     */
    private function installDocumentTypes()
    {
        // get list of types
        $list = new DocType\Listing();
        $list->load();

        $skipInstall = false;
        $elementName = 'Formbuilder Email';

        foreach ($list->getDocTypes() as $type) {
            if ($type->getName() === $elementName) {
                $skipInstall = true;
                break;
            }
        }

        if ($skipInstall) {
            return false;
        }

        $type = DocType::create();

        $data = [
            'name'       => $elementName,
            'module'     => 'FormBuilderBundle',
            'controller' => 'Email',
            'action'     => 'email',
            'template'   => 'FormBuilderBundle:Email:email.html.twig',
            'type'       => 'email',
            'priority'   => 0
        ];

        $type->setValues($data);
        $type->save();
    }
}
