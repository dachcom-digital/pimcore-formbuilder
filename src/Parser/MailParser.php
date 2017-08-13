<?php

namespace FormBuilderBundle\Parser;

use FormBuilderBundle\Form\FormValuesTransformer;
use Pimcore\Mail;
use Pimcore\Model\Document\Email;
use Symfony\Component\Form\FormInterface;
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
     * @var FormValuesTransformer
     */
    protected $formValuesTransformer;

    /**
     * MailParser constructor.
     *
     * @param EngineInterface $templating
     * @param FormValuesTransformer $formValuesTransformer
     */
    public function __construct(EngineInterface $templating, FormValuesTransformer $formValuesTransformer)
    {
        $this->templating = $templating;
        $this->formValuesTransformer = $formValuesTransformer;
    }

    /**
     * @param Email               $mailTemplate
     * @param       FormInterface $form
     *
     * @return Mail
     */
    public function create(Email $mailTemplate, FormInterface $form, $locale)
    {
        $mail = new Mail();

        $disableDefaultMailBody = (bool)$mailTemplate->getProperty('mail_disable_default_mail_body');

        $ignoreFields = (string)$mailTemplate->getProperty('mail_ignore_fields');
        $ignoreFields = array_map('trim', explode(',', $ignoreFields));

        $fieldData = $form->getData()->getFields($ignoreFields);
        $fieldValues = $this->formValuesTransformer->transformData($form, $fieldData, $locale);

        $this->parseMailRecipients($mailTemplate, $fieldValues);
        $this->parseMailSender($mailTemplate, $fieldValues);
        $this->parseSubject($mailTemplate, $fieldValues);
        $this->setMailPlaceholders($mail, $fieldValues, $disableDefaultMailBody);

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
     * @param array $fieldValues
     */
    private function parseSubject(Email $mailTemplate, $fieldValues = [])
    {
        $realSubject = $mailTemplate->getSubject();

        preg_match_all("/\%(.+?)\%/", $realSubject, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($fieldValues as $formField) {
                    if (empty($formField['value'])) {
                        continue;
                    }

                    if ($formField['name'] == $inputValue) {
                        $realSubject = str_replace(
                            $matches[0][$key],
                            $this->getSingleRenderedValue($formField['value'], ', '),
                            $realSubject
                        );
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
     */
    private function setMailPlaceholders(Mail $mail, $data, $disableDefaultMailBody)
    {
        //allow access to all form placeholders
        foreach ($data as $label => $field) {

            if (empty($field['value'])) {
                continue;
            }

            $mail->setParam(!empty($field['email_label']) ? $field['email_label'] : $field['label'], $this->getSingleRenderedValue($field['value']));
        }

        if ($disableDefaultMailBody === FALSE) {
            $mail->setParam('body', $this->getBodyTemplate($data));
        }
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function getBodyTemplate($data)
    {
        $html = $this->templating->render(
            '@FormBuilder/Email/formData.html.twig',
            ['fields' => $data]);

        return $html;
    }

    /**
     * Extract Placeholder Data from given String like %email% and compare it with given form data.
     *
     * @param $str
     * @param $fieldValues
     *
     * @return mixed|string
     */
    private function extractPlaceHolder($str, $fieldValues)
    {
        $extractedValue = $str;

        preg_match_all("/\%(.+?)\%/", $str, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($fieldValues as $formField) {
                    if ($formField['name'] == $inputValue) {
                        $str = str_replace($matches[0][$key], $formField['value'], $str);
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