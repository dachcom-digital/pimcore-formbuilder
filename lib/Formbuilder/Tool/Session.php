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
            $formBuilderSession->tmpData = [];
        }

        return $formBuilderSession;
    }

    /**
     * @param int $formId
     *
     * @return array
     */
    public static function getFromTmpSession( $formId )
    {
        $session = self::getSession();

        if( !isset( $session->tmpData[ $formId ] ) )
        {
            return [];
        }

        return $session->tmpData[ $formId ];

    }

    /**
     * @param int $formId
     * @param string $fieldName
     * @param string $uuid
     * @param string $name
     *
     * @return mixed
     */
    public static function addToTmpSession( $formId, $fieldName, $uuid, $name)
    {
        $session = self::getSession();

        if( !isset( $session->tmpData[ $formId ][ $fieldName ] ) )
        {
            $session->tmpData[ $formId ][ $fieldName ] = [];
        }

        $session->tmpData[ $formId ][ $fieldName ][ $uuid ] = $name;

        return $session->tmpData[ $formId ];
    }

    /**
     * @param int $formId
     * @param string $fieldName
     * @param string $uuid
     *
     * @return array
     */
    public static function removeFromTmpSession( $formId, $fieldName = NULL, $uuid = NULL )
    {
        $session = self::getSession();

        if( !isset( $session->tmpData[ $formId ][ $fieldName ] ) )
        {
            return [];
        }

        //reset all attachment fields in form
        if( $uuid === NULL )
        {
            $session->tmpData[ $formId ] = [];
            return $session->tmpData[ $formId ];
        }

        if( isset( $session->tmpData[ $formId ][ $fieldName ][ $uuid ] ) )
        {
            unset( $session->tmpData[ $formId ][ $fieldName ][ $uuid ] );

            if( empty( $session->tmpData[ $formId ][ $fieldName ] ) )
            {
                unset( $session->tmpData[ $formId ][ $fieldName ] );
            }
        }

        return $session->tmpData[ $formId ][ $fieldName ];
    }
}