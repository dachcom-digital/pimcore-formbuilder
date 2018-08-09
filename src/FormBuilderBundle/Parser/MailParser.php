<?php

namespace FormBuilderBundle\Parser;

use FormBuilderBundle\Form\FormValuesBeautifier;
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
     * @var FormValuesBeautifier
     */
    protected $formValuesBeautifier;

    /**
     * MailParser constructor.
     *
     * @param EngineInterface      $templating
     * @param FormValuesBeautifier $formValuesBeautifier
     */
    public function __construct(
        EngineInterface $templating,
        FormValuesBeautifier $formValuesBeautifier
    ) {
        $this->templating = $templating;
        $this->formValuesBeautifier = $formValuesBeautifier;
    }

    /**
     * @param Email         $mailTemplate
     * @param FormInterface $form
     * @param               $locale
     *
     * @return Mail
     * @throws \Exception
     */
    public function create(Email $mailTemplate, FormInterface $form, $locale)
    {
        $mail = new Mail();

        $disableDefaultMailBody = (bool)$mailTemplate->getProperty('mail_disable_default_mail_body');

        $ignoreFields = (string)$mailTemplate->getProperty('mail_ignore_fields');
        $ignoreFields = array_map('trim', explode(',', $ignoreFields));

        $fieldValues = $this->formValuesBeautifier->transformData($form, $ignoreFields, $locale);

        $this->parseMailRecipients($mailTemplate, $fieldValues);
        $this->parseMailSender($mailTemplate, $fieldValues);
        $this->parseReplyTo($mailTemplate, $fieldValues);
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
        $parsedTo = $this->extractPlaceHolder($mailTemplate->getTo(), $data);
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
    private function parseReplyTo(Email $mailTemplate, $data = [])
    {
        $replyTo = $mailTemplate->getReplyTo();
        $parsedReplyTo = $this->extractPlaceHolder($replyTo, $data);

        $mailTemplate->setReplyTo($parsedReplyTo);
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
                    if ($this->isEmptyFormField($formField['value'])) {
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
     * @param      $fieldValues
     * @param      $disableDefaultMailBody
     */
    private function setMailPlaceholders(Mail $mail, $fieldValues, $disableDefaultMailBody)
    {
        //allow access to all form placeholders
        foreach ($fieldValues as $formField) {
            if ($this->isEmptyFormField($formField['value'])) {
                continue;
            }

            $mail->setParam($formField['name'], $this->getSingleRenderedValue($formField['value']));
        }

        if ($disableDefaultMailBody === false) {
            $mail->setParam('body', $this->getBodyTemplate($fieldValues));
        }
    }

    /**
     * @param mixed $formFieldValue
     *
     * @return bool
     */
    private function isEmptyFormField($formFieldValue)
    {
        return empty($formFieldValue) && $formFieldValue !== 0 && $formFieldValue !== '0';
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
                        $value = $formField['value'];
                        //if is array, use first value since this is the best what we can do...
                        if (is_array($value)) {
                            $value = reset($value);
                        }
                        $str = str_replace($matches[0][$key], $value, $str);
                    }
                }

                //replace with '' if not found.
                $extractedValue = str_replace($matches[0][$key], '', $str);
            }
        }

        //remove invalid commas
        $extractedValue = trim(implode(',', preg_split('@,@', $extractedValue, null, PREG_SPLIT_NO_EMPTY)));

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