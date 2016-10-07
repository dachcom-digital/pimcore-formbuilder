<?php

namespace Formbuilder\Lib;

use \Pimcore\Model;
use \Pimcore\Mail;

Class Mailer {

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

            $disableDefaultMailBody = (bool) $mailTemplate->getProperty('mail_disable_default_mail_body');

            self::setMailPlaceholders( $attributes['data'], $mail, $disableDefaultMailBody );
            self::setMailRecipients( $attributes['data'], $mailTemplate );

            $from = $mailTemplate->getFrom();

            if( !empty($from) ) {
                $mail->setFrom( $from );
            }

            $mail->addCc( $mailTemplate->getCcAsArray() );
            $mail->addBcc( $mailTemplate->getBccAsArray() );

            $mail->setSubject( $mailTemplate->getSubject() );
            $mail->setDocument( $mailTemplate );

            $mail->send();

            return TRUE;

        }

        return FALSE;

    }

    /**
     * @param array $data
     * @param \Pimcore\Model\Document\Email $mailTemplate
     */
    private static function setMailRecipients($data = array(), $mailTemplate) {

        $to = $mailTemplate->getTo();

        preg_match_all("/\%(.+?)\%/", $to, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0){

            foreach ($matches[1] as $key => $inputValue)
            {
                foreach( $data as $formFieldName => $formFieldValue)
                {
                    if( $formFieldName == $inputValue)
                    {
                        $to = str_replace( $matches[0][$key], $formFieldValue, $to);
                    }
                }

                //replace with '' if not found.
                $to = str_replace( $matches[0][$key], '', $to);
            }
        }

        //remove invalid commas
        $to = trim( implode(',', preg_split('@,@', $to, NULL, PREG_SPLIT_NO_EMPTY ) ) );

        $mailTemplate->setTo( $to );

    }

    private static function setMailPlaceholders($data, $mail, $disableDefaultMailBody )
    {
        if( $disableDefaultMailBody === FALSE )
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
            $html .= '<td width="20%"><strong>' . $label . ':</strong></td>' . "\n";
            $html .= '<td width="70%">' . $data . '</td>' . "\n";
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

