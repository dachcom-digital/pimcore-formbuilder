<?php

namespace FormBuilderBundle\Parser;

use Pimcore\Mail;
use Pimcore\Model\Document\Email;
use Symfony\Component\Templating\EngineInterface;

class MailParser
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var Email
     */
    protected $mailTemplate;


    /**
     * MailParser constructor.
     *
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param Email $mailTemplate
     * @param       $attributes
     *
     * @return Mail
     */
    public function create(Email $mailTemplate, $attributes)
    {
        $mail = new Mail();

        $disableDefaultMailBody = (bool)$mailTemplate->getProperty('mail_disable_default_mail_body');
        $ignoreFields = (string)$mailTemplate->getProperty('mail_ignore_fields');

        $this->parseMailRecipients($mailTemplate, $attributes['data']);
        $this->parseMailSender($mailTemplate, $attributes['data']);
        $this->parseSubject($mailTemplate, $attributes['data']);

        $this->setMailPlaceholders($mail, $attributes['data'], $disableDefaultMailBody, $ignoreFields);

        $mail->setDocument($mailTemplate);

        return $mail;
    }

    /**
     * @param Email $mailTemplate
     * @param array $data
     */
    private function parseMailRecipients(Email $mailTemplate, $data = [])
    {
        $to = $mailTemplate->getTo();
        $parsedTo = $this->extractPlaceHolder($to, $data);

        $mailTemplate->setTo($parsedTo);
    }

    /**
     * @param Email $mailTemplate
     * @param array $data
     */
    private function parseMailSender(Email $mailTemplate, $data = [])
    {
        $from = $mailTemplate->getFrom();
        $parsedFrom = $this->extractPlaceHolder($from, $data);

        $mailTemplate->setFrom($parsedFrom);
    }

    /**
     * @param Email $mailTemplate
     * @param array $data
     */
    private function parseSubject(Email $mailTemplate,  $data = [])
    {
        $realSubject = $mailTemplate->getSubject();

        preg_match_all("/\%(.+?)\%/", $realSubject, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($data as $formFieldName => $formFieldValue) {
                    if (empty($formFieldValue['value'])) {
                        continue;
                    }

                    if ($formFieldName == $inputValue) {
                        $realSubject = str_replace($matches[0][$key], $this->getSingleRenderedValue($formFieldValue['value'], ', '), $realSubject);
                    }
                }

                //replace with '' if not found.
                $realSubject = str_replace($matches[0][$key], '', $realSubject);
            }
        }

        $mailTemplate->setSubject($realSubject);

    }

    /**
     * @param Mail $mail
     * @param      $data
     * @param      $disableDefaultMailBody
     * @param      $ignoreFields
     */
    private function setMailPlaceholders(Mail $mail, $data, $disableDefaultMailBody, $ignoreFields)
    {
        $ignoreFields = array_map('trim', explode(',', $ignoreFields));

        //allow access to all form placeholders
        foreach ($data as $label => $field) {
            //ignore fields!
            if (in_array($label, $ignoreFields) || empty($field['value'])) {
                continue;
            }

            $mail->setParam($label, $this->getSingleRenderedValue($field['value']));
        }

        if ($disableDefaultMailBody === FALSE) {
            $mail->setParam('body', $this->parseHtml($data, $ignoreFields));
        }
    }

    /**
     * @param $data
     * @param $ignoreFields
     *
     * @return string
     */
    private function parseHtml($data, $ignoreFields)
    {
        $renderData = [];

        foreach ($data as $label => $fieldData) {
            //ignore fields!
            if (in_array($label, $ignoreFields)) {
                continue;
            }

            //@todo: implement form translations.
            //$data = $this->getSingleRenderedValue($fieldData['value']);
            $data = $fieldData;

            if (empty($data)) {
                continue;
            }

            $renderData[] = ['label' => $label, 'value' => $fieldData];
        }

        $html = $this->templating->render('@FormBuilder/Email/formData.html.twig', ['data' => $renderData]);

        return $html;
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
     * @param        $field
     * @param string $separator
     *
     * @return string
     */
    private function getSingleRenderedValue($field, $separator = '<br>')
    {
        $data = '';
        if (is_array($field)) {
            foreach ($field as $f) {
                $data .= $this->parseStringForOutput($f) . $separator;
            }
        } else {
            $data = $this->parseStringForOutput($field);
        }

        return $data;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function parseStringForOutput($string = '')
    {
        if (strstr($string, "\n")) {
            return nl2br($string);
        }

        return $string;
    }

}