<?php

namespace Formbuilder\Lib\Email;

use \Pimcore\Mail;

Class FormbuilderMail extends Mail {

    private $ignoreFields = [];

    public function setIgnoreFields( $fields = [])
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

                $this->setParam($label, $this->getSingleRenderedValue( $field ) );
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

    private function getSingleRenderedValue( $field )
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