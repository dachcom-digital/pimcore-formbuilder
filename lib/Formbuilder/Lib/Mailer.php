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
            $mail->setParam('body', self::parseHtml( $attributes['data'] ) );
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

    private static function parseHtml( $data )
    {

        $html = '<table>';

        foreach( $data as $label => $field ) {

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

            if( empty( $data ) )
            {
                continue;
            }

            $html .= '<tr>';

            $html .= '<td><strong>' . $label . ':</strong></td>';
            $html .= '<td>' . $data . '</td>';

            $html .= '</tr>';

        }

        $html .= '</table>';

        return $html;

    }

}

