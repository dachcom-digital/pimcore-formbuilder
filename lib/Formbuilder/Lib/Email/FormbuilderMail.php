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
        //allow access to all form placeholders
        foreach( $data as $label => $field )
        {
            //ignore fields!
            if( in_array( $label, $this->ignoreFields ) || empty( $field['value'] ))
            {
                continue;
            }

            $this->setParam( $label, $this->getSingleRenderedValue( $field['value'] ) );
        }

        if( $disableDefaultMailBody === FALSE )
        {
            $this->setParam('body', self::parseHtml( $data, $this->ignoreFields ) );
        }
    }

    /**
     * @param array $data [label,value]
     *
     * @return string
     */
    private function parseHtml( $data )
    {
        $view = new \Pimcore\View();
        $view->setScriptPath(
            [
                FORMBUILDER_PATH . '/views/scripts/',
                PIMCORE_WEBSITE_PATH . '/views/scripts',
            ]
        );

        $renderData = [];

        foreach( $data as $label => $fieldData )
        {
            //ignore fields!
            if( in_array( $label, $this->ignoreFields ) )
            {
                continue;
            }

            $data = $this->getSingleRenderedValue( $fieldData['value'] );

            if( empty( $data ) )
            {
                continue;
            }

            $renderData[] = ['label' => $fieldData['label'], 'value' => $data];
        }

        $html = $view->partial('formbuilder/form/partials/defaultFormData.php', ['data' => $renderData]);

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
    public function parseSubject($subject = '', $data = [] )
    {
        $realSubject = $subject;

        preg_match_all("/\%(.+?)\%/", $realSubject, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0)
        {
            foreach ($matches[1] as $key => $inputValue )
            {
                foreach( $data as $formFieldName => $formFieldValue )
                {
                    if( empty( $formFieldValue['value'] ) )
                    {
                        continue;
                    }

                    if( $formFieldName == $inputValue)
                    {
                        $realSubject = str_replace( $matches[0][$key], $this->getSingleRenderedValue( $formFieldValue['value'], ', '), $realSubject);
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