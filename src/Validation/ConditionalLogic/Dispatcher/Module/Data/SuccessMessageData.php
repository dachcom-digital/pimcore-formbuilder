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

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

use FormBuilderBundle\Tool\LocaleDataMapper;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Snippet;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuccessMessageData implements DataInterface
{
    public const IDENTIFIER_STRING = 'string';
    public const IDENTIFIER_SNIPPET = 'snippet';
    public const IDENTIFIER_REDIRECT = 'redirect';
    public const IDENTIFIER_REDIRECT_EXTERNAL = 'redirect_external';

    protected array $data = [];

    public function __construct(
        protected LocaleDataMapper $localeDataMapper,
        protected TranslatorInterface $translator
    ) {
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

    public function getIdentifiedData(?string $locale): Document|Snippet|string|null
    {
        if ($this->isStringSuccess()) {
            return $this->getString($locale);
        }

        if ($this->isSnippet()) {
            return $this->getSnippet($locale);
        }

        if ($this->isDocumentRedirect()) {
            return $this->getDocument($locale);
        }

        if ($this->isExternalRedirect()) {
            return $this->getExternalRedirect();
        }

        return null;
    }

    public function isStringSuccess(): bool
    {
        return isset($this->data[self::IDENTIFIER_STRING]) && !empty($this->data[self::IDENTIFIER_STRING]);
    }

    public function getString(?string $locale): ?string
    {
        return $this->isStringSuccess() ? $this->translator->trans((string) $this->data[self::IDENTIFIER_STRING], [], null, $locale) : null;
    }

    public function isSnippet(): bool
    {
        return isset($this->data[self::IDENTIFIER_SNIPPET]) && !empty($this->data[self::IDENTIFIER_SNIPPET]);
    }

    public function getSnippet(?string $locale): ?Snippet
    {
        if (!$this->isSnippet()) {
            return null;
        }

        $snippetId = $this->localeDataMapper->mapHref($locale, $this->data[self::IDENTIFIER_SNIPPET]);

        if (is_numeric($snippetId)) {
            return Snippet::getById($snippetId);
        }

        return null;
    }

    public function isDocumentRedirect(): bool
    {
        return isset($this->data[self::IDENTIFIER_REDIRECT]) && !empty($this->data[self::IDENTIFIER_REDIRECT]);
    }

    public function getDocument(?string $locale): ?Document
    {
        if (!$this->isDocumentRedirect()) {
            return null;
        }

        $documentId = $this->localeDataMapper->mapHref($locale, $this->data[self::IDENTIFIER_REDIRECT]);

        if (is_numeric($documentId)) {
            return Document::getById($documentId);
        }

        return null;
    }

    public function isExternalRedirect(): bool
    {
        return isset($this->data[self::IDENTIFIER_REDIRECT_EXTERNAL])
            && !empty($this->data[self::IDENTIFIER_REDIRECT_EXTERNAL])
            && str_starts_with($this->data[self::IDENTIFIER_REDIRECT_EXTERNAL], 'http');
    }

    public function getExternalRedirect(): ?string
    {
        if (!$this->isExternalRedirect()) {
            return null;
        }

        return $this->data[self::IDENTIFIER_REDIRECT_EXTERNAL];
    }

    public function hasFlashMessage(): bool
    {
        return $this->isDocumentRedirect() && isset($this->data['flashMessage']) && !empty($this->data['flashMessage']);
    }

    public function getFlashMessage(?string $locale): ?string
    {
        if (!$this->hasFlashMessage()) {
            return null;
        }

        return $this->translator->trans((string) $this->data['flashMessage'], [], null, $locale);
    }
}
