<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

use FormBuilderBundle\Tool\LocaleDataMapper;

class MailBehaviourData implements DataInterface
{
    public const IDENTIFIER_MAIL_TEMPLATE = 'mailTemplate';
    public const IDENTIFIER_RECIPIENT = 'recipient';

    protected LocaleDataMapper $localeDataMapper;
    private array $data = [];

    public function __construct(LocaleDataMapper $localeDataMapper)
    {
        $this->localeDataMapper = $localeDataMapper;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function hasData(): bool
    {
        return !empty($this->data);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function hasRecipient(): bool
    {
        return isset($this->data[self::IDENTIFIER_RECIPIENT]) && !empty($this->data[self::IDENTIFIER_RECIPIENT]);
    }

    public function getRecipient(): ?string
    {
        return $this->hasRecipient() ? $this->data[self::IDENTIFIER_RECIPIENT] : null;
    }

    public function hasMailTemplate(): bool
    {
        return isset($this->data[self::IDENTIFIER_MAIL_TEMPLATE]) && !empty($this->data[self::IDENTIFIER_MAIL_TEMPLATE]);
    }

    public function getMailTemplateId(string $locale): ?int
    {
        if (!$this->hasMailTemplate()) {
            return null;
        }

        return (int) $this->localeDataMapper->mapHref($locale, $this->data[self::IDENTIFIER_MAIL_TEMPLATE]);
    }
}
