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
     * @param string        $locale
     *
     * @return Mail
     *
     * @throws \Exception
     */
    public function create(Email $mailTemplate, FormInterface $form, $locale)
    {
        $mail = new Mail();

        $disableDefaultMailBody = (bool) $mailTemplate->getProperty('mail_disable_default_mail_body');

        $ignoreFields = (string) $mailTemplate->getProperty('mail_ignore_fields');
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
     * @param Email  $mailTemplate
     * @param array  $fieldValues
     * @param string $prefix
     */
    private function parseSubject(Email $mailTemplate, $fieldValues = [], $prefix = '')
    {
        $realSubject = $mailTemplate->getSubject();

        preg_match_all("/\%(.+?)\%/", $realSubject, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($fieldValues as $formField) {
                    if ($formField['field_type'] === 'container') {
                        if ($formField['type'] === 'fieldset' && is_array($formField['fields']) && count($formField['fields']) === 1) {
                            $this->parseSubject($mailTemplate, $formField['fields'][0], $formField['name']);
                            $realSubject = $mailTemplate->getSubject();
                        }
                        // repeatable container values as placeholders is unsupported.
                        continue;
                    }

                    if ($this->isEmptyFormField($formField['value'])) {
                        continue;
                    }

                    $name = empty($prefix) ? $formField['name'] : sprintf('%s_%s', $prefix, $formField['name']);
                    if ($name == $inputValue) {
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
     * @param Mail   $mail
     * @param array  $fieldValues
     * @param bool   $disableDefaultMailBody
     * @param string $prefix
     */
    private function setMailPlaceholders(Mail $mail, array $fieldValues, bool $disableDefaultMailBody, string $prefix = '')
    {
        //allow access to all form placeholders
        foreach ($fieldValues as $formField) {
            if ($formField['field_type'] === 'container') {
                if (is_array($formField['fields']) && count($formField['fields']) > 0) {
                    foreach ($formField['fields'] as $groupIndex => $group) {
                        $prefix = sprintf('%s_%s', $formField['name'], $groupIndex);
                        // do not add numeric index to fieldset (not repeatable)
                        // to allow better placeholder versatility
                        if ($formField['type'] === 'fieldset') {
                            $prefix = $formField['name'];
                        }
                        $this->setMailPlaceholders($mail, $group, $disableDefaultMailBody, $prefix);
                    }
                }

                continue;
            }
            if ($this->isEmptyFormField($formField['value'])) {
                continue;
            }

            $paramName = empty($prefix) ? $formField['name'] : sprintf('%s_%s', $prefix, $formField['name']);
            $mail->setParam($paramName, $this->getSingleRenderedValue($formField['value']));
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
     * @param array $data
     *
     * @return string
     */
    private function getBodyTemplate($data)
    {
        $html = $this->templating->render(
            '@FormBuilder/Email/formData.html.twig',
        ['fields' => $data]
            );

        return $html;
    }

    /**
     * Extract Placeholder Data from given String like %email% and compare it with given form data.
     *
     * @param string $str
     * @param array  $fieldValues
     * @param string $prefix
     *
     * @return mixed|string
     */
    private function extractPlaceHolder($str, $fieldValues, $prefix = '')
    {
        $extractedValue = $str;

        preg_match_all("/\%(.+?)\%/", $str, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($fieldValues as $formField) {
                    // container values as placeholders is unsupported.
                    if ($formField['field_type'] === 'container') {
                        if ($formField['type'] === 'fieldset' && is_array($formField['fields']) && count($formField['fields']) === 1) {
                            $str = $this->extractPlaceHolder($str, $formField['fields'][0], $formField['name']);
                        }
                        // repeatable container values as placeholders is unsupported.
                        continue;
                    }

                    $name = empty($prefix) ? $formField['name'] : sprintf('%s_%s', $prefix, $formField['name']);
                    if ($name == $inputValue) {
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
        $fragments = preg_split('@,@', $extractedValue, null, PREG_SPLIT_NO_EMPTY);
        $fragmentsGlued = is_array($fragments) ? implode(',', $fragments) : $fragments;
        $extractedValue = is_string($fragmentsGlued) ? trim($fragmentsGlued) : $fragmentsGlued;

        return $extractedValue;
    }

    /**
     * @param mixed  $field
     * @param string $separator
     *
     * @return string
     */
    private function getSingleRenderedValue($field, $separator = '<br>')
    {
        $data = '';
        if (is_array($field)) {
            foreach ($field as $k => $f) {
                $data .= $this->parseStringForOutput($f);
                if ($k + 1 !== count($field)) {
                    $data .= $separator;
                }
            }
        } else {
            $data = $this->parseStringForOutput($field);
        }

        // pimcore email log does not get stored if value is a true boolean.
        if (is_bool($data)) {
            $data = $data === true ? 1 : 0;
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
