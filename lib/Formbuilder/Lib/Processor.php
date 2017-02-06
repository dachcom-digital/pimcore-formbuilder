<?php

namespace Formbuilder\Lib;

use \Pimcore\Model;

use \Formbuilder\Model\Form;
use \Formbuilder\Lib\Email\FormbuilderMail;
use \Formbuilder\Lib\Form\File\PackageHandler;

use Formbuilder\Tool\Session;

Class Processor
{
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

    /**
     * @param \Zend_Form $form
     * @param Form       $formData
     * @param null       $mailTemplateId
     * @param null       $copyMailTemplateId
     *
     * @return bool
     */
    public function parse(\Zend_Form $form, Form $formData, $mailTemplateId = NULL, $copyMailTemplateId = NULL)
    {
        if (empty($mailTemplateId)) {
            $this->log('no valid mail template given.');

            return FALSE;
        }

        $data = $this->getFormValues($form);

        //set upload data!
        $packageHandler = new PackageHandler();

        $boundedFieldFiles = Session::getFromTmpSession($formData->getId());

        if (is_array($boundedFieldFiles)) {
            foreach ($boundedFieldFiles as $fieldName => $boundedFilePackage) {
                $asset = $packageHandler->createZipAsset($boundedFilePackage, $formData->getName(), $fieldName, $mailTemplateId);

                //remove tmp element from session!
                Session::removeFromTmpSession($formData->getId(), $fieldName);

                if ($asset instanceof \Pimcore\Model\Asset) {
                    $http = 'http://';
                    if (!empty($_SERVER['HTTPS'])) {
                        $http = 'https://';
                    }

                    $websiteUrl = $http . \Pimcore\Tool::getHostname();

                    //get translated label for files!
                    $fileLabel = $fieldName;

                    if (isset($data[$fieldName])) {
                        $fileLabel = $data[$fieldName]['label'];
                    }

                    $data[$fieldName] = ['label' => $fileLabel, 'value' => $websiteUrl . $asset->getRealFullPath()];
                }
            }
        }

        //allow third parties to manipulate form data!
        $cmdEv = \Pimcore::getEventManager()->trigger(
            'formbuilder.form.preSendData',
            NULL,
            [
                'formData' => $formData,
                'data'     => $data,
            ]
        );

        if ($cmdEv->stopped()) {
            $customData = $cmdEv->last();

            if (is_array($customData)) {
                $data = $customData;
            }
        }

        try {
            $send = $this->sendForm($mailTemplateId, ['data' => $data]);

            if ($send === TRUE) {
                $this->isValid = TRUE;

                //send copy!
                if ($this->sendCopy === TRUE) {
                    try {
                        $send = $this->sendForm($copyMailTemplateId, ['data' => $data]);

                        if ($send !== TRUE) {
                            $this->log('copy mail not sent.');
                            $this->isValid = FALSE;
                        }
                    } catch (\Exception $e) {
                        $this->log('copy mail sent error: ' . $e->getMessage());
                        $this->isValid = FALSE;
                    }
                }
            } else {
                $this->log('mail not sent.');
            }
        } catch (\Exception $e) {
            $this->log('mail sent error: ' . $e->getMessage());
            $this->isValid = FALSE;
        }
    }

    /**
     * @param int   $mailTemplateId
     * @param array $attributes
     *
     * @throws \Exception
     * @returns bool
     */
    private function sendForm($mailTemplateId = 0, $attributes = [])
    {
        $mailTemplate = Model\Document::getById($mailTemplateId);

        if (!$mailTemplate instanceof Model\Document\Email) {
            return FALSE;
        }

        $this->setMailRecipients($attributes['data'], $mailTemplate);
        $this->setMailSender($attributes['data'], $mailTemplate);

        $disableDefaultMailBody = (bool)$mailTemplate->getProperty('mail_disable_default_mail_body');
        $ignoreFieldData = (string)$mailTemplate->getProperty('mail_ignore_fields');

        $ignoreFields = array_map('trim', explode(',', $ignoreFieldData));

        $mail = new FormbuilderMail();
        $mail->setDocument($mailTemplate);

        $mail->setIgnoreFields($ignoreFields);
        $mail->parseSubject($mailTemplate->getSubject(), $attributes['data']);
        $mail->setMailPlaceholders($attributes['data'], $disableDefaultMailBody);

        $mail->addCc($mailTemplate->getCcAsArray());
        $mail->addBcc($mailTemplate->getBccAsArray());

        //allow 3rd parties to hook into processed email
        $cmdEv = \Pimcore::getEventManager()->trigger(
            'formbuilder.form.preSendMail',
            NULL,
            [
                'formbuilderMail' => $mail
            ]
        );

        if ($cmdEv->stopped()) {
            $customMail = $cmdEv->last();
            if ($customMail instanceof \Formbuilder\Lib\Email\FormbuilderMail) {
                $mail = $customMail;
            }
        }

        $mail->send();

        return TRUE;
    }

    /**
     * @param array                         $data
     * @param \Pimcore\Model\Document\Email $mailTemplate
     */
    private function setMailRecipients($data = [], $mailTemplate)
    {

        $to = $mailTemplate->getTo();
        $parsedTo = $this->extractPlaceHolder($to, $data);

        $mailTemplate->setTo($parsedTo);
    }

    /**
     * @param array                         $data
     * @param \Pimcore\Model\Document\Email $mailTemplate
     */
    private function setMailSender($data = [], $mailTemplate)
    {

        $from = $mailTemplate->getFrom();
        $parsedFrom = $this->extractPlaceHolder($from, $data);

        $mailTemplate->setFrom($parsedFrom);
    }

    /**
     * Extract Placeholder Data from given String like %email% and compare it with given form data.
     *
     * @param $str
     * @param $data
     *
     * @return mixed|string
     */
    private function extractPlaceHolder($str, $data)
    {
        $extractedValue = $str;

        preg_match_all("/\%(.+?)\%/", $str, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($data as $formFieldName => $formFieldValue) {
                    if ($formFieldName == $inputValue) {
                        $str = str_replace($matches[0][$key], $formFieldValue['value'], $str);
                    }
                }

                //replace with '' if not found.
                $extractedValue = str_replace($matches[0][$key], '', $str);
            }
        }

        //remove invalid commas
        $extractedValue = trim(implode(',', preg_split('@,@', $extractedValue, NULL, PREG_SPLIT_NO_EMPTY)));

        return $extractedValue;
    }

    /**
     * @param bool $state
     */
    public function setSendCopy($state = FALSE)
    {
        $this->sendCopy = $state;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @param bool $asArray
     *
     * @return array|string
     */
    public function getMessages($asArray = TRUE)
    {
        return $asArray ? $this->messages : implode(',', $this->messages);
    }

    /**
     * @param string $message
     */
    private function log($message = '')
    {
        $this->messages[] = $message;
    }

    /**
     * Flat all subForm values to single key value array.
     *
     * @param \Zend_form $form
     * @param array      $dat
     * @param bool       $allowEmptyValues
     *
     * @return array
     */
    private function getFormValues($form, $dat = [], $allowEmptyValues = TRUE)
    {
        foreach ($form->getElementsAndSubFormsOrdered() as $element) {
            if ($element instanceof \Zend_Form) {
                $dat = $this->getFormValues($element, $dat, $allowEmptyValues);
            } elseif ($element instanceof \Zend_Form_SubForm) {
                $dat = $this->getFormValues($element, $dat, $allowEmptyValues);
            } elseif ($element instanceof \Zend_Form_Element) {
                $label = $element->getLabel();
                $value = $element->getValue();
                $name = $element->getName();

                //skip private name convention
                if (substr($name, 0, 1) === '_') {
                    continue;
                }

                if (empty($label)) {
                    $label = $name;
                }

                if (empty($value) && $allowEmptyValues === FALSE) {
                    continue;
                }

                if ($element instanceof \Zend_Form_Element_Multi) {
                    $_multiValue = [];

                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $_multiValue[] = $element->getMultiOption($val);
                        }
                    } else {
                        $_multiValue[] = $element->getMultiOption($value);
                    }
                    if (!empty($_multiValue)) {
                        $value = implode(', ', $_multiValue);
                    }
                }

                $dat[$name] = ['label' => $label, 'value' => $value];
            }
        }

        return $dat;
    }
}