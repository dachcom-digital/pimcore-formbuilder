<?php

namespace Formbuilder\Lib;

use \Pimcore\Model;
use \Pimcore\Mail;

Class Mailer {

    private static $messages = array();

    /**
     * @param int    $mailTemplateId
     * @param array $attributes
     *
     * @throws \Exception
     * @returns bool
     */
    public static function sendForm( $mailTemplateId = 0, $attributes = array() )
    {
        $mailTemplate = Model\Document::getById( $mailTemplateId );

        if( $mailTemplate instanceof Model\Document\Email )
        {
            $mail = new Mail();

            $mailTemplateType = $mailTemplate->getProperty('mail_disable_default_mail_body');

            self::setMailPlaceholders( $attributes['data'], $mail, $mailTemplateType );

            $mail->setDocument( $mailTemplate );
            $mail->send();

            $successMessage = $mailTemplate->getProperty('mail_successfully_sent');

            if (!empty($successMessage))
            {
                self::$messages[] = $successMessage;
            }

            return TRUE;

        }

        return FALSE;

    }

    public static function getMessages()
    {
        return self::$messages;
    }

    private static function setMailPlaceholders($data, $mail, $mailTemplateType )
    {
        if( is_null( $mailTemplateType ) )
        {
            $mail->setParam('body', self::parseHtml( $data ) );
        }
        else
        {
            foreach( $data as $label => $field )
            {
                $mail->setParam($label, self::getSingleRenderedValue( $field ) );
            }
        }

    }

    private static function parseHtml( $data )
    {
        $html = '<table>';

        foreach( $data as $label => $field )
        {
            $data = self::getSingleRenderedValue( $field );

            if( empty( $data ) )
            {
                continue;
            }

            $html .= '<tr>' . "\n";
                $html .= '<td><strong>' . $label . ':</strong></td>' . "\n";
                $html .= '<td>' . $data . '</td>' . "\n";
            $html .= '</tr>' . "\n";

        }

        $html .= '</table>';

        return $html;

    }

    private static function getSingleRenderedValue( $field )
    {
        $data = '';

        if( is_array( $field ) )
        {
            foreach( $field as $f )
            {
                $data .= $f . '<br>';
            }
        }
        else
        {
            $data = $field;
        }

        return $data;
    }

}

