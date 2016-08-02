<?php

namespace Formbuilder;

use Pimcore\Model\Tool\Setup;
use Pimcore\Model\Translation\Admin;
use Pimcore\API\Plugin as PluginLib;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    public function __construct($jsPaths = null, $cssPaths = null, $alternateIndexDir = null)
    {
        parent::__construct($jsPaths, $cssPaths);

        define('FORMBUILDER_PATH', PIMCORE_PLUGINS_PATH . '/Formbuilder');
        define('FORMBUILDER_DEFAULT_ERROR_PATH', FORMBUILDER_PATH . '/static/lang/errors');
        define('FORMBUILDER_INSTALL_PATH', FORMBUILDER_PATH . '/install');
        define('FORMBUILDER_DATA_PATH', PIMCORE_WEBSITE_VAR . '/formbuilder');
    }

    public function preDispatch($e)
    {
        $e->getTarget()->registerPlugin(new Controller\Plugin\Frontend());
    }

    public static function needsReloadAfterInstall()
    {
        return false;
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


    public static function isInstalled()
    {
        $result = null;

        $db = \Pimcore\Db::get();

        try
        {
            $result = $db->query("SELECT * FROM `formbuilder_forms`") or die ("table formbuilder_forms doesn't exist.");
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