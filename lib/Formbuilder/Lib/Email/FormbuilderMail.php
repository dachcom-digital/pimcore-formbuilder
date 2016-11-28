<?php

namespace Formbuilder\Lib\Email;

use \Pimcore\Mail;

Class FormbuilderMail extends Mail {

    private $ignoreFields = [];

    public function setIgnoreFields($fields = [])
    {
        $this->ignoreFields = $fields;
    }

    public function setMailPlaceholders( $data, $disableDefaultMailBody )
    {
        if( $disableDefaultMailBody === FALSE )
        {
            $this->setParam('body', self::parseHtml( $data, $this->ignoreFields ) );
        }
        else
        {
            foreach( $data as $label => $field )
            {
                //ignore fields!
                if( in_array( $label, $this->ignoreFields ) )
                {
                    continue;
                }

                $this->setParam( $label, $this->getSingleRenderedValue( $field ) );
            }
        }
    }

    private function parseHtml( $data )
    {
        $html = '<table>';

        foreach( $data as $label => $field )
        {
            //ignore fields!
            if( in_array( $label, $this->ignoreFields ) )
            {
                continue;
            }

            $data = $this->getSingleRenderedValue( $field );

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

    /**
     *
     * Transform placeholders into values.
     *
     * @param string $subject
     * @param array  $data
     *
     * @throws \Zend_Mail_Exception
     */
    public function parseSubject($subject = '', $data = array() )
    {
        $realSubject = $subject;

        preg_match_all("/\%(.+?)\%/", $realSubject, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0)
        {
            foreach ($matches[1] as $key => $inputValue )
            {
                foreach( $data as $formFieldName => $formFieldValue )
                {
                    if( $formFieldName == $inputValue)
                    {
                        $realSubject = str_replace( $matches[0][$key], $this->getSingleRenderedValue( $formFieldValue, ', '), $realSubject);
                    }
                }

                //replace with '' if not found.
                $realSubject = str_replace( $matches[0][$key], '', $realSubject);
            }
        }

        $this->setSubject( $realSubject );

    }

    private function getSingleRenderedValue( $field, $separator = '<br>' )
    {
        $data = '';

        if( is_array( $field ) )
        {
            foreach( $field as $f )
            {
                $data .= $f . $separator;
            }
        }
        else
        {
            $data = $field;
        }

        return $data;
    }

}