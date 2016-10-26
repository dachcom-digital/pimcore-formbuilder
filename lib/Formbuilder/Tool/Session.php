<?php

namespace Formbuilder\Tool;

use Pimcore\Tool\Session as PimcoreSession;

class Session {

    /**
     * @return \Zend_Session_Namespace
     */
    public static function getSession()
    {
        $formBuilderSession = PimcoreSession::get('Formbuilder');

        if( !isset( $formBuilderSession->tmpData ) )
        {
            $formBuilderSession->tmpData = array();
        }

        return $formBuilderSession;
    }

    /**
     * @param int $formId
     *
     * @return array
     */
    public static function getFromTmpSession ( $formId )
    {
        $session = self::getSession();

        if( !isset( $session->tmpData[ $formId ] ) )
        {
            return array();
        }

        return $session->tmpData[ $formId ];

    }

    /**
     * @param int $formId
     * @param string $uuid
     * @param string $name
     *
     * @return mixed
     */
    public static function addToTmpSession( $formId, $uuid, $name)
    {
        $session = self::getSession();

        if( !isset( $session->tmpData[ $formId ] ) )
        {
            $session->tmpData[ $formId ] = array();
        }

        $session->tmpData[ $formId ][ $uuid ] = $name;

        return $session->tmpData[ $formId ];
    }

    /**
     * @param int $formId
     * @param string $uuid
     *
     * @return array
     */
    public static function removeFromTmpSession( $formId, $uuid = NULL )
    {
        $session = self::getSession();

        if( !isset( $session->tmpData[ $formId ] ) )
        {
            return array();
        }

        //reset complete form
        if( $uuid === NULL )
        {
            $session->tmpData[ $formId ] = array();
            return $session->tmpData[ $formId ];
        }

        if( isset( $session->tmpData[ $formId ][ $uuid ] ) )
        {
            unset( $session->tmpData[ $formId ][ $uuid ] );
        }

        return $session->tmpData[ $formId ];
    }
}