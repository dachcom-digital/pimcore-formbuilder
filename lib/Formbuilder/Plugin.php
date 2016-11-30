<?php

namespace Formbuilder;

use Pimcore\Model\Tool\Setup;
use Pimcore\Model\Property;
use Pimcore\Model\Translation\Admin;
use Pimcore\API\Plugin as PluginLib;

use Formbuilder\Tool\File;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    public function __construct($jsPaths = null, $cssPaths = null, $alternateIndexDir = null)
    {
        parent::__construct($jsPaths, $cssPaths);

        define('FORMBUILDER_PATH', PIMCORE_PLUGINS_PATH . '/Formbuilder');
        define('FORMBUILDER_DEFAULT_ERROR_PATH', FORMBUILDER_PATH . '/static/lang/errors');
        define('FORMBUILDER_INSTALL_PATH', FORMBUILDER_PATH . '/install');
        define('FORMBUILDER_DATA_PATH', PIMCORE_WEBSITE_VAR . '/formbuilder');
    }

    public function init()
    {
        parent::init();

        \Pimcore::getEventManager()->attach('system.maintenance', array($this, 'maintenanceJob'));
    }

    public function preDispatch($e)
    {
        $e->getTarget()->registerPlugin(new Controller\Plugin\Frontend());
    }

    /**
     * Hook called when maintenance script is called
     */
    public function maintenanceJob()
    {
        if ( !self::isInstalled() )
        {
            return FALSE;
        }

        File::setupTmpFolder();

        foreach( File::getFolderContent( File::getFilesFolder(), 86400 ) as $file )
        {
            \Pimcore\Logger::log('Remove formbuilder file: ' . $file);
            File::removeDir($file);
        }

        foreach( File::getFolderContent( File::getChunksFolder(), 86400 ) as $file )
        {
            \Pimcore\Logger::log('Remove formbuilder file: ' . $file);
            File::removeDir($file);
        }

        foreach( File::getFolderContent( File::getZipFolder(), 86400 ) as $file )
        {
            \Pimcore\Logger::log('Remove formbuilder file: ' . $file);
            File::removeDir($file);
        }

        return TRUE;

    }

    public static function needsReloadAfterInstall()
    {
        return FALSE;
    }

    public static function uninstall()
    {
        $db = \Pimcore\Db::get();
        $db->query('DROP TABLE `formbuilder_forms`');

        recursiveDelete( FORMBUILDER_DATA_PATH );

        if (!self::isInstalled())
        {
            $statusMessage = 'Formbuilder Plugin successfully uninstalled.';
        }
        else
        {
            $statusMessage = 'Formbuilder Plugin could not be uninstalled';
        }

        return $statusMessage;

    }

    public static function install()
    {
        $setup = new Setup();
        $setup->insertDump( FORMBUILDER_INSTALL_PATH . '/sql/install.sql' );

        if( !is_dir( FORMBUILDER_DATA_PATH ) )
        {
            mkdir( FORMBUILDER_DATA_PATH );
            mkdir(FORMBUILDER_DATA_PATH . '/lang');
            mkdir(FORMBUILDER_DATA_PATH . '/form');
        }

        $csv = PIMCORE_PLUGINS_PATH . '/Formbuilder/install/translations/data.csv';
        Admin::importTranslationsFromFile($csv, true, \Pimcore\Tool\Admin::getLanguages());

        //create folder for upload storage!
        $folderName = 'formdata';

        if( !\Pimcore\Model\Asset\Folder::getByPath('/' . $folderName) instanceof \Pimcore\Model\Asset\Folder)
        {
            $folder = new \Pimcore\Model\Asset\Folder();
            $folder->setCreationDate ( time() );
            $folder->setLocked(true);
            $folder->setUserOwner (1);
            $folder->setUserModification (0);
            $folder->setParentId(1);
            $folder->setFilename($folderName);
            $folder->save();
        }

        //install properties
        self::installProperties();

        self::installDocumentTypes();

        if (self::isInstalled())
        {
            $statusMessage = 'Plugin has been successfully installed.<br>Please reload pimcore!';
        }
        else
        {
            $statusMessage = 'Formbuilder Plugin could not be installed.';
        }

        return $statusMessage;
    }

    private static function installProperties()
    {
        $properties = [

            'mail_disable_default_mail_body' => [
                'ctype'         => 'document',
                'type'          => 'bool',
                'name'          => 'Mail: Use custom template fields layout',
                'description'   => 'If the mail_disable_default_mail_body property is defined and checked, you need to add your own data to the mail template. You can use all the field names as placeholder.'
            ],
            'mail_successfully_sent' => [
                'ctype'         => 'document',
                'type'          => 'document',
                'name'          => 'Mail: Message after Submit',
                'description'   => 'Use the mail_successfully_sent property to define a message after the form has been successfully sent. There are three options: "String", "Snippet", "Dokument"'

            ],
            'mail_ignore_fields' => [
                'ctype'         => 'document',
                'type'          => 'text',
                'name'          => 'Mail: Ignored Fields in Email',
                'description'   => 'In some cases, you don\'t want to send specific fields via mail. Add one or multiple (comma separated) fields as string.'
            ],

        ];

        foreach( $properties as $key => $propertyConfig)
        {
            $defProperty = Property\Predefined::getByKey( $key );

            if( $defProperty instanceof Property\Predefined)
            {
                continue;
            }

            $property = new Property\Predefined();
            $property->setKey( $key );
            $property->setType( $propertyConfig['type'] );
            $property->setName( $propertyConfig['name'] );

            $property->setDescription( $propertyConfig['description'] );
            $property->setCtype( $propertyConfig['ctype'] );
            $property->setInheritable(FALSE);
            $property->save();

        }

    }

    private static function installDocumentTypes()
    {
        // get list of types
        $list = new \Pimcore\Model\Document\DocType\Listing();
        $list->load();

        $skipInstall = FALSE;
        $elementName = 'Formbuilder Email';

        foreach( $list->getDocTypes() as $type )
        {
            if( $type->getName() === $elementName )
            {
                $skipInstall = TRUE;
                break;
            }
        }

        if( $skipInstall )
        {
            return FALSE;
        }

        $type = \Pimcore\Model\Document\DocType::create();

        $data = [
            'name'          => $elementName,
            'module'        => 'Formbuilder',
            'controller'    => 'Email',
            'action'        => 'default',
            'template'      => '/formbuilder/email/default.php',
            'type'          => 'email',
            'priority'      => 0
        ];

        $type->setValues($data);
        $type->save();

    }

    public static function isInstalled()
    {
        $result = null;

        $db = \Pimcore\Db::get();

        try
        {
            $result = $db->query("SELECT * FROM `formbuilder_forms`") or die ('Table formbuilder_forms doesn\'t exist.');
        }
        catch (\Zend_Db_Statement_Exception $e)
        {

        }

        return !empty($result);
    }

    /**
     * @param string $language
     * @return string path to the translation file relative to plugin directory
     */
    public static function getTranslationFile($language)
    {
        if(file_exists(FORMBUILDER_PATH . '/static/texts/' . $language . '.csv'))
        {
            return '/Formbuilder/static/texts/' . $language . '.csv';
        }

        return '/Formbuilder/static/texts/en.csv';
    }

}