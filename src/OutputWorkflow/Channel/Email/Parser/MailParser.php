<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\OutputWorkflow\Channel\Email\Parser;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\MailEditor\Parser\PlaceholderParserInterface;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;
use FormBuilderBundle\Stream\File;
use League\Flysystem\FilesystemOperator;
use Pimcore\Mail;
use Pimcore\Model\Document\Email;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;

class MailParser
{
    public function __construct(
        protected EngineInterface $templating,
        protected FormValuesOutputApplierInterface $formValuesOutputApplier,
        protected PlaceholderParserInterface $placeholderParser,
        protected FilesystemOperator $formBuilderFilesStorage
    ) {
    }

    /**
     * @throws \Exception
     */
    public function create(Email $mailTemplate, FormInterface $form, array $channelConfiguration, array $context): Mail
    {
        $mail = new Mail();

        $locale = $context['locale'] ?? null;
        $doubleOptInSession = $context['doubleOptInSession'] ?? null;

        $allowAttachments = $channelConfiguration['allowAttachments'];
        $disableDefaultMailBody = $channelConfiguration['disableDefaultMailBody'];
        $forcePlainText = (bool) $channelConfiguration['forcePlainText'];

        $ignoreFields = is_null($channelConfiguration['ignoreFields']) ? [] : $channelConfiguration['ignoreFields'];

        $fieldValues = $this->formValuesOutputApplier->applyForChannel($form, $ignoreFields, 'mail', $locale);

        $systemFieldValues = $fieldValues;
        if ($doubleOptInSession instanceof DoubleOptInSessionInterface) {
            $systemFieldValues = array_merge($systemFieldValues, $this->createDoubleOptInSessionValues($doubleOptInSession, ['email'], true));
        }

        $this->parseMailRecipients($mailTemplate, $systemFieldValues);
        $this->parseMailSender($mailTemplate, $systemFieldValues);
        $this->parseReplyTo($mailTemplate, $systemFieldValues);
        $this->parseSubject($mailTemplate, $systemFieldValues);
        $this->setMailPlaceholders($mail, $fieldValues);

        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        if ($disableDefaultMailBody === false) {
            $mailLayout = $this->getMailLayout($channelConfiguration, $forcePlainText);
            $this->setMailBodyPlaceholder($mail, $form, $doubleOptInSession, $fieldValues, $mailLayout, $forcePlainText ? 'text' : 'html');
        }

        $attachments = [];
        if ($allowAttachments === true && $formData->hasAttachments()) {
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
        $parsedCC = $this->extractPlaceHolder($mailTemplate->getCc(), $data);
        $mailTemplate->setCC($parsedCC);
        $parsedBCC = $this->extractPlaceHolder($mailTemplate->getBcc(), $data);
        $mailTemplate->setBcc($parsedBCC);
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

        if (count($matches[1]) === 0) {
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

    protected function setMailBodyPlaceholder(
        Mail $mail,
        FormInterface $form,
        ?DoubleOptInSessionInterface $doubleOptInSession,
        array $fieldValues,
        ?string $mailLayout,
        string $layoutType
    ): void {
        $doubleOptInSessionValues = [];
        if ($doubleOptInSession instanceof DoubleOptInSessionInterface) {
            $doubleOptInSessionValues = $this->createDoubleOptInSessionValues(
                $doubleOptInSession,
                ['email', 'token', 'creation_date', 'additional_data']
            );
        }

        if ($mailLayout === null) {
            $body = $this->templating->render(
                '@FormBuilder/email/form_data.html.twig',
                [
                    'fields'                => $fieldValues,
                    'double_opt_in_session' => $doubleOptInSessionValues
                ]
            );
        } else {
            if ($doubleOptInSession instanceof DoubleOptInSessionInterface) {
                $fieldValues['double_opt_in_session'] = $doubleOptInSessionValues;
            }

            $body = $this->placeholderParser->replacePlaceholderWithOutputData($mailLayout, $form, $fieldValues, $layoutType);
        }

        $mail->setParam('body', $body);
    }

    protected function parseMailAttachment(Mail $mail, array $attachments): void
    {
        /** @var File $attachmentFile */
        foreach ($attachments as $attachmentFile) {
            try {
                $mail->attach($this->formBuilderFilesStorage->read($attachmentFile->getPath()), $attachmentFile->getName());
            } catch (\Exception $e) {
                // fail silently.
            }
        }
    }

    /**
     * Extract Placeholder Data from given String like %email% and compare it with given form data.
     */
    protected function extractPlaceHolder(string $str, array $fieldValues): mixed
    {
        $availablePlaceholder = $this->findPlaceholderValues($fieldValues);

        preg_match_all('/\%(.+?)\%/', $str, $matches);

        if (count($matches[1]) === 0) {
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

        // remove invalid commas
        $fragments = preg_split('@,@', $str, -1, PREG_SPLIT_NO_EMPTY);
        $fragmentsGlued = is_array($fragments) ? implode(',', $fragments) : $fragments;
        $extractedValue = is_string($fragmentsGlued) ? trim($fragmentsGlued) : $fragmentsGlued;

        return $extractedValue;
    }

    protected function getSingleRenderedValue(mixed $field, string $separator = '<br>'): string
    {
        $data = '';
        if (is_array($field)) {
            $i = 0;
            foreach ($field as $subField) {
                $i++;
                $data .= $this->parseFieldDataForOutput($subField);
                if ($i !== count($field)) {
                    $data .= $separator;
                }
            }
        } else {
            $data = $this->parseFieldDataForOutput($field);
        }

        return $data;
    }

    protected function parseFieldDataForOutput(mixed $fieldData): string
    {
        if (str_contains($fieldData, "\n")) {
            return nl2br($fieldData);
        }

        // pimcore email log does not get stored if value is a true boolean.
        if (is_bool($fieldData)) {
            return $fieldData === true ? '1' : '0';
        }

        return (string) $fieldData;
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

    protected function getMailLayout(array $channelConfiguration, bool $forcePlainText): ?string
    {
        $data = null;

        if (array_key_exists('mailLayoutData', $channelConfiguration) && is_array($channelConfiguration['mailLayoutData'])) {
            $layoutTypeKey = $forcePlainText ? 'text' : 'html';
            $data = $channelConfiguration['mailLayoutData'][$layoutTypeKey] ?? null;
        }

        return $data === '' ? null : $data;
    }

    protected function createDoubleOptInSessionValues(?DoubleOptInSessionInterface $doubleOptInSession, array $validFields, bool $createFieldValueScheme = false): array
    {
        if (!$doubleOptInSession instanceof DoubleOptInSessionInterface) {
            return [];
        }

        $data = [];

        foreach ($validFields as $validField) {
            $data[] = match ($validField) {
                'email'           => $this->createDoubleOptInSessionValue('email', $doubleOptInSession->getEmail(), $createFieldValueScheme),
                'token'           => $this->createDoubleOptInSessionValue('token', $doubleOptInSession->getTokenAsString(), $createFieldValueScheme),
                'creation_date'   => $this->createDoubleOptInSessionValue('creation_date', $doubleOptInSession->getCreationDate(), $createFieldValueScheme),
                'additional_data' => $this->createDoubleOptInSessionValue('additional_data', $doubleOptInSession->getAdditionalData(), $createFieldValueScheme),
                default           => null,
            };
        }

        return array_merge([], ...$data);
    }

    protected function createDoubleOptInSessionValue(string $name, mixed $value, bool $createFieldValueScheme = false): mixed
    {
        if ($createFieldValueScheme === false) {
            return [$name => $value];
        }

        return [
            sprintf('double_opt_in_session_%s', $name) => [
                'label'       => sprintf('double_opt_in_session_%s', $name),
                'email_label' => sprintf('double_opt_in_session_%s', $name),
                'name'        => sprintf('double_opt_in_session_%s', $name),
                'value'       => $value,
                'field_type'  => 'simple',
                'type'        => 'text'
            ]
        ];
    }
}
