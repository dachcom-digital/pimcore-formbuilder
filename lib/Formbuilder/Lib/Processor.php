<?php

namespace Formbuilder\Lib;

use \Pimcore\Model;

use \Formbuilder\Model\Form;
use \Formbuilder\Lib\Email\FormbuilderMail;
use \Formbuilder\Lib\Form\File\PackageHandler;

use Formbuilder\Tool\Session;

Class Processor {

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var bool
     */
    private $isValid = FALSE;

    /**
     * @var bool
     */
    private $sendCopy = FALSE;

    public function parse( \Zend_Form $form, Form $formData, $mailTemplateId = NULL, $copyMailTemplateId = NULL )
    {
        if( empty($mailTemplateId) )
        {
            $this->log('no valid mail template given.');
            return FALSE;
        }

        $data = $form->getValues();

        //set upload data!
        $packageHandler = new PackageHandler();

        $boundedFieldFiles = Session::getFromTmpSession( $formData->getId() );

        if( is_array( $boundedFieldFiles ) )
        {
            foreach( $boundedFieldFiles as $fieldName => $boundedFilePackage)
            {
                $asset = $packageHandler->createZipAsset( $boundedFilePackage, $formData->getName(), $fieldName, $mailTemplateId );

                //remove tmp element from session!
                Session::removeFromTmpSession( $formData->getId(), $fieldName );

                if( $asset instanceof \Pimcore\Model\Asset)
                {
                    $http = 'http://';
                    if ( !empty( $_SERVER['HTTPS'] ) )
                    {
                        $http = 'https://';
                    }

                    $websiteUrl = $http . \Pimcore\Tool::getHostname();
                    $data[ $fieldName ] = $websiteUrl . $asset->getRealFullPath();
                }
            }

        }

        $send = $this->sendForm( $mailTemplateId, [ 'data' => $data ] );

        if( $send === TRUE )
        {
            $this->isValid = TRUE;

            //send copy!
            if( $this->sendCopy === TRUE )
            {
                $send = $this->sendForm( $copyMailTemplateId, [ 'data' => $data ] );

                if( $send !== TRUE )
                {
                    $this->log('copy mail not sent.');
                    $this->isValid = FALSE;
                }
            }
        }
        else
        {
            $this->log('mail not sent.');
        }
    }

    /**
     * @param int    $mailTemplateId
     * @param array $attributes
     *
     * @throws \Exception
     * @returns bool
     */
    private function sendForm( $mailTemplateId = 0, $attributes = [] )
    {
        $mailTemplate = Model\Document::getById( $mailTemplateId );

        if( !$mailTemplate instanceof Model\Document\Email )
        {
            return FALSE;
        }

        $this->setMailRecipients( $attributes['data'], $mailTemplate );

        $disableDefaultMailBody = (bool) $mailTemplate->getProperty('mail_disable_default_mail_body');
        $ignoreFieldData = (string) $mailTemplate->getProperty('mail_ignore_fields');

        $ignoreFields = explode(',', $ignoreFieldData);

        $mail = new FormbuilderMail();
        $mail->setIgnoreFields( $ignoreFields );
        $mail->setMailPlaceholders( $attributes['data'], $disableDefaultMailBody );

        $from = $mailTemplate->getFrom();

        if( !empty($from) )
        {
            $mail->setFrom( $from );
        }

        $mail->addCc( $mailTemplate->getCcAsArray() );
        $mail->addBcc( $mailTemplate->getBccAsArray() );

        $mail->setSubject( $mailTemplate->getSubject() );
        $mail->setDocument( $mailTemplate );

        $mail->send();

        return TRUE;

    }

    /**
     * @param array $data
     * @param \Pimcore\Model\Document\Email $mailTemplate
     */
    private function setMailRecipients($data = [], $mailTemplate) {

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


    public function setSendCopy( $state = FALSE )
    {
        $this->sendCopy = $state;
    }

    public function isValid()
    {
        return $this->isValid;
    }

    public function getMessages( $asArray = TRUE )
    {
        return $asArray ? $this->messages : implode( ',', $this->messages );
    }

    private function log( $message = '' )
    {
        $this->messages[] = $message;
    }

}

