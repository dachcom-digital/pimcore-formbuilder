<?php

namespace Formbuilder;

use Pimcore\API\Plugin as PluginLib;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    public static function install()
    {
        $db = \Pimcore\Db::get();

        $db->query("CREATE TABLE IF NOT EXISTS `formbuilder_forms` (
		`id` INT NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL ,
		`date` INT NULL ,
        PRIMARY KEY  (`id`),
        UNIQUE KEY `name` (`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        if (self::isInstalled())
        {
            $statusMessage = "Plugin successfully installed. <br/>Please reload pimcore";
        }
        else
        {
            $statusMessage = "Fourmbuilder Plugin could not be installed";
        }
        return $statusMessage;
    }

    public static function needsReloadAfterInstall()
    {
        return false;
    }

    public static function uninstall()
    {
        $db = \Pimcore\Db::get();
        $db->query("DROP TABLE `formbuilder_forms`");

        $rep = PIMCORE_PLUGINS_PATH . "/Formbuilder/data/";

        if (is_dir($rep))
        {
            $dir = opendir($rep);
            while ($f = readdir($dir))
            {
                if(substr($f,0,4)=="main")
                {
                    if (file_exists($rep . $f))
                    {
                        unlink($rep . $f);
                    }
                }
            }
        }

        $rep = PIMCORE_PLUGINS_PATH . "/Formbuilder/data/form/";

        if (is_dir($rep))
        {
            $dir = opendir($rep);
            while ($f = readdir($dir))
            {
                if(substr($f,0,4)=="form")
                {
                    if (file_exists($rep . $f))
                    {
                        unlink($rep . $f);
                    }
                }
            }
        }

        $rep = PIMCORE_PLUGINS_PATH . "/Formbuilder/data/lang/";

        if (is_dir($rep))
        {
            $dir = opendir($rep);
            while ($f = readdir($dir))
            {
                if(substr($f,0,4)=="form")
                {
                    if (file_exists($rep . $f))
                    {
                        unlink($rep . $f);
                    }
                }
            }
        }

        if (!self::isInstalled())
        {
            $statusMessage = "Plugin successfully uninstalled.";
        }
        else
        {
            $statusMessage = "Formbuilder Plugin could not be uninstalled";
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
        catch (Zend_Db_Statement_Exception $e)
        {

        }

        return !empty($result);
    }

    public function preDispatch()
    {

    }

    /**
     * @param string $language
     * @return string path to the translation file relative to plugin directory
     */
    public static function getTranslationFile($language)
    {
        if(file_exists(PIMCORE_PLUGINS_PATH . "/Formbuilder/texts/" . $language . ".csv"))
        {
            return "/Formbuilder/texts/" . $language . ".csv";
        }

        return "/Formbuilder/texts/en.csv";
        
    }

}