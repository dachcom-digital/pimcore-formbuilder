<?php

namespace FormBuilderBundle\Parser;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\MailEditor\Parser\PlaceholderParserInterface;
use FormBuilderBundle\Stream\AttachmentStreamInterface;
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
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    /**
     * @var PlaceholderParserInterface
     */
    protected $placeholderParser;

    /**
     * @var AttachmentStreamInterface
     */
    protected $attachmentStream;

    /**
     * @param EngineInterface                  $templating
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     * @param PlaceholderParserInterface       $placeholderParser
     * @param AttachmentStreamInterface        $attachmentStream
     */
    public function __construct(
        EngineInterface $templating,
        FormValuesOutputApplierInterface $formValuesOutputApplier,
        PlaceholderParserInterface $placeholderParser,
        AttachmentStreamInterface $attachmentStream
    ) {
        $this->templating = $templating;
        $this->formValuesOutputApplier = $formValuesOutputApplier;
        $this->placeholderParser = $placeholderParser;
        $this->attachmentStream = $attachmentStream;
    }

    /**
     * @param Email         $mailTemplate
     * @param FormInterface $form
     * @param array         $attachments
     * @param string        $locale
     * @param bool          $isCopy
     *
     * @return Mail
     *
     * @throws \Exception
     */
    public function create(Email $mailTemplate, FormInterface $form, array $attachments, $locale, $isCopy)
    {
        $mail = new Mail();

        $disableDefaultMailBody = (bool) $mailTemplate->getProperty('mail_disable_default_mail_body');

        $ignoreFields = (string) $mailTemplate->getProperty('mail_ignore_fields');
        $ignoreFields = array_map('trim', explode(',', $ignoreFields));

        $initialCharset = $mail->getCharset();
        $fieldValues = $this->formValuesOutputApplier->applyForChannel($form, $ignoreFields, 'mail', $locale);

        $this->parseMailRecipients($mailTemplate, $fieldValues);
        $this->parseMailSender($mailTemplate, $fieldValues);
        $this->parseReplyTo($mailTemplate, $fieldValues);
        $this->parseSubject($mailTemplate, $fieldValues);
        $this->setMailPlaceholders($mail, $fieldValues);

        if ($disableDefaultMailBody === false) {
            $mailLayout = $form->getData()->getMailLayoutBasedOnLocale($isCopy === false ? 'main' : 'copy', $locale);
            $this->setMailBodyPlaceholder($mail, $form, $fieldValues, $mailLayout);
        }

        $this->parseMailAttachment($mail, $attachments);

        $mail->setDocument($mailTemplate);

        // fix charset
        if ($mail->getCharset() === null) {
            $mail->setCharset($initialCharset);
        }

        return $mail;
    }

    /**
     * @param Email $mailTemplate
     * @param array $data
     */
    protected function parseMailRecipients(Email $mailTemplate, $data = [])
    {
        $parsedTo = $this->extractPlaceHolder($mailTemplate->getTo(), $data);
        $mailTemplate->setTo($parsedTo);
    }

    /**
     * @param Email $mailTemplate
     * @param array $data
     */
    protected function parseMailSender(Email $mailTemplate, $data = [])
    {
        $from = $mailTemplate->getFrom();
        $parsedFrom = $this->extractPlaceHolder($from, $data);

        $mailTemplate->setFrom($parsedFrom);
    }

    /**
     * @param Email $mailTemplate
     * @param array $data
     */
    protected function parseReplyTo(Email $mailTemplate, $data = [])
    {
        $replyTo = $mailTemplate->getReplyTo();
        $parsedReplyTo = $this->extractPlaceHolder($replyTo, $data);

        $mailTemplate->setReplyTo($parsedReplyTo);
    }

    /**
     * @param Email $mailTemplate
     * @param array $fieldValues
     */
    protected function parseSubject(Email $mailTemplate, $fieldValues = [])
    {
        $realSubject = $mailTemplate->getSubject();
        $availableValues = $this->findPlaceholderValues($fieldValues);

        preg_match_all('/\%(.+?)\%/', $realSubject, $matches);

        if (!isset($matches[1]) || count($matches[1]) === 0) {
            return;
        }

        foreach ($matches[1] as $key => $inputValue) {
            if (!array_key_exists($inputValue, $availableValues)) {
                //replace with '' if not found.
                $realSubject = str_replace($matches[0][$key], '', $realSubject);

                continue;
            }

            $realSubject = str_replace(
                $matches[0][$key],
                $this->getSingleRenderedValue($availableValues[$inputValue], ', '),
                $realSubject
            );
        }

        $mailTemplate->setSubject($realSubject);
    }

    /**
     * @param Mail  $mail
     * @param array $fieldValues
     */
    protected function setMailPlaceholders(Mail $mail, array $fieldValues)
    {
        $availablePlaceholder = $this->findPlaceholderValues($fieldValues);
        foreach ($availablePlaceholder as $placeHolderName => $placeholderValue) {
            $mail->setParam($placeHolderName, $this->getSingleRenderedValue($placeholderValue));
        }
    }

    /**
     * @param Mail          $mail
     * @param FormInterface $form
     * @param array         $fieldValues
     * @param null|string   $mailLayout
     */
    protected function setMailBodyPlaceholder(Mail $mail, FormInterface $form, array $fieldValues, $mailLayout = null)
    {
        if ($mailLayout === null) {
            $body = $this->templating->render(
                '@FormBuilder/Email/formData.html.twig',
                ['fields' => $fieldValues]
            );
        } else {
            $body = $this->placeholderParser->replacePlaceholderWithOutputData($mailLayout, $form, $fieldValues);
        }

        $mail->setParam('body', $body);
    }

    /**
     * @param Mail  $mail
     * @param array $attachments
     */
    protected function parseMailAttachment(Mail $mail, array $attachments)
    {
        foreach ($attachments as $attachmentFileInfo) {
            try {
                $attachment = new \Swift_Attachment();
                $attachment->setBody(file_get_contents($attachmentFileInfo['path']));
                $attachment->setFilename($attachmentFileInfo['name']);
                $mail->attach($attachment);
            } catch (\Exception $e) {
                // fail silently.
            }

            $this->attachmentStream->removeAttachmentByFileInfo($attachmentFileInfo);
        }
    }

    /**
     * Extract Placeholder Data from given String like %email% and compare it with given form data.
     *
     * @param string $str
     * @param array  $fieldValues
     *
     * @return mixed|string
     */
    protected function extractPlaceHolder($str, $fieldValues)
    {
        $availablePlaceholder = $this->findPlaceholderValues($fieldValues);

        preg_match_all('/\%(.+?)\%/', $str, $matches);

        if (!isset($matches[1]) || count($matches[1]) === 0) {
            return $str;
        }

        foreach ($matches[1] as $key => $inputValue) {
            if (!array_key_exists($inputValue, $availablePlaceholder)) {
                //replace with '' if not found.
                $str = str_replace($matches[0][$key], '', $str);

                continue;
            }

            $value = $availablePlaceholder[$inputValue];

            //if is array, use first value since this is the best what we can do...
            if (is_array($value)) {
                $value = reset($value);
            }

            $str = str_replace($matches[0][$key], $value, $str);
        }

        //remove invalid commas
        $fragments = preg_split('@,@', $str, null, PREG_SPLIT_NO_EMPTY);
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
    protected function getSingleRenderedValue($field, $separator = '<br>')
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
    protected function parseStringForOutput($string = '')
    {
        if (strstr($string, "\n")) {
            return nl2br($string);
        }

        return $string;
    }

    /**
     * @param array  $fieldValues
     * @param string $prefix
     * @param array  $values
     *
     * @return array
     */
    protected function findPlaceholderValues(array $fieldValues, string $prefix = '', array &$values = [])
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

                        $this->findPlaceholderValues($group, $prefix, $values);
                    }

                    $prefix = '';
                }

                continue;
            }

            $paramName = empty($prefix) ? $formField['name'] : sprintf('%s_%s', $prefix, $formField['name']);
            $values[$paramName] = $formField['value'];
        }

        return $values;
    }
}
