<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Email\Parser;

use FormBuilderBundle\Model\FormDefinitionInterface;
use Pimcore\Mail;
use Pimcore\Model\Document\Email;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\MailEditor\Parser\PlaceholderParserInterface;
use FormBuilderBundle\Stream\AttachmentStreamInterface;

class MailParser
{
    protected EngineInterface $templating;
    protected Email $mailTemplate;
    protected FormValuesOutputApplierInterface $formValuesOutputApplier;
    protected PlaceholderParserInterface $placeholderParser;
    protected AttachmentStreamInterface $attachmentStream;

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
     * @throws \Exception
     */
    public function create(Email $mailTemplate, FormInterface $form, array $channelConfiguration, string $locale): Mail
    {
        $mail = new Mail();

        $allowAttachments = $channelConfiguration['allowAttachments'];
        $disableDefaultMailBody = $channelConfiguration['disableDefaultMailBody'];
        $forcePlainText = (bool) $channelConfiguration['forcePlainText'];

        $ignoreFields = is_null($channelConfiguration['ignoreFields']) ? [] : $channelConfiguration['ignoreFields'];

        $hasIsCopyFlag = isset($channelConfiguration['legacyIsCopy']);
        $isCopy = $hasIsCopyFlag && $channelConfiguration['legacyIsCopy'] === true;

        $fieldValues = $this->formValuesOutputApplier->applyForChannel($form, $ignoreFields, 'mail', $locale);

        $this->parseMailRecipients($mailTemplate, $fieldValues);
        $this->parseMailSender($mailTemplate, $fieldValues);
        $this->parseReplyTo($mailTemplate, $fieldValues);
        $this->parseSubject($mailTemplate, $fieldValues);
        $this->setMailPlaceholders($mail, $fieldValues);

        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        if ($disableDefaultMailBody === false) {
            $mailLayout = $this->getMailLayout($formData->getFormDefinition(), $channelConfiguration, $isCopy, $locale);
            $this->setMailBodyPlaceholder($mail, $form, $fieldValues, $mailLayout);
        }

        $attachments = [];
        if ($formData->hasAttachments() && $allowAttachments === true) {
            $attachments = $formData->getAttachments();
        }

        $this->parseMailAttachment($mail, $attachments);

        $mailTemplate->setProperty('mail_disable_default_mail_body', 'text', $disableDefaultMailBody);
        $mailTemplate->setProperty('mail_force_plain_text', 'checkbox', $forcePlainText);

        $mail->setDocument($mailTemplate);

        return $mail;
    }

    protected function parseMailRecipients(Email $mailTemplate, array $data = []): void
    {
        $parsedTo = $this->extractPlaceHolder($mailTemplate->getTo(), $data);
        $mailTemplate->setTo($parsedTo);
    }

    protected function parseMailSender(Email $mailTemplate, array $data = []): void
    {
        $from = $mailTemplate->getFrom();
        $parsedFrom = $this->extractPlaceHolder($from, $data);

        $mailTemplate->setFrom($parsedFrom);
    }

    protected function parseReplyTo(Email $mailTemplate, array $data = []): void
    {
        $replyTo = $mailTemplate->getReplyTo();
        $parsedReplyTo = $this->extractPlaceHolder($replyTo, $data);

        $mailTemplate->setReplyTo($parsedReplyTo);
    }

    protected function parseSubject(Email $mailTemplate, array $fieldValues = []): void
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

    protected function setMailPlaceholders(Mail $mail, array $fieldValues): void
    {
        $availablePlaceholder = $this->findPlaceholderValues($fieldValues);
        foreach ($availablePlaceholder as $placeHolderName => $placeholderValue) {
            $mail->setParam($placeHolderName, $this->getSingleRenderedValue($placeholderValue));
        }
    }

    protected function setMailBodyPlaceholder(Mail $mail, FormInterface $form, array $fieldValues, ?string $mailLayout = null): void
    {
        if ($mailLayout === null) {
            $body = $this->templating->render(
                '@FormBuilder/email/form-data.html.twig',
                ['fields' => $fieldValues]
            );
        } else {
            $body = $this->placeholderParser->replacePlaceholderWithOutputData($mailLayout, $form, $fieldValues);
        }

        $mail->setParam('body', $body);
    }

    protected function parseMailAttachment(Mail $mail, array $attachments): void
    {
        foreach ($attachments as $attachmentFileInfo) {
            try {
                $mail->attach(file_get_contents($attachmentFileInfo['path']), $attachmentFileInfo['name']);
            } catch (\Exception $e) {
                // fail silently.
            }

            $this->attachmentStream->removeAttachmentByFileInfo($attachmentFileInfo);
        }
    }

    /**
     * Extract Placeholder Data from given String like %email% and compare it with given form data.
     */
    protected function extractPlaceHolder(string $str, array $fieldValues): mixed
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

    protected function getSingleRenderedValue(mixed $field, string $separator = '<br>'): string
    {
        $data = '';
        if (is_array($field)) {
            $i = 0;
            foreach ($field as $k => $f) {
                $i++;
                $data .= $this->parseStringForOutput($f);
                if ($i !== count($field)) {
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

    protected function parseStringForOutput(string $string = ''): string
    {
        if (str_contains($string, "\n")) {
            return nl2br($string);
        }

        return $string;
    }

    protected function findPlaceholderValues(array $fieldValues, string $prefix = '', array &$values = []): array
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

    public function getMailLayout(FormDefinitionInterface $formDefinition, array $channelConfiguration, bool $isCopy, ?string $locale): ?string
    {
        if (!empty($channelConfiguration['mailLayoutData'])) {
            return $channelConfiguration['mailLayoutData'];
        }

        $formMailLayout = $formDefinition->getMailLayout();

        if ($formMailLayout !== null) {
            return $this->getFallbackMailLayoutBasedOnLocale($formMailLayout, $isCopy === false ? 'main' : 'copy', $locale);
        }

        return null;
    }

    public function getFallbackMailLayoutBasedOnLocale(array $mailLayout, string $mailType, ?string $locale = null): ?string
    {
        if (!isset($mailLayout[$mailType])) {
            return null;
        }

        return $mailLayout[$mailType][$locale] ?? $mailLayout[$mailType]['default'] ?? null;
    }
}
