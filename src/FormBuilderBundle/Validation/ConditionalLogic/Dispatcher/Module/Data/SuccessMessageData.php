<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

use FormBuilderBundle\Tool\HrefLocaleMapper;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Snippet;
use Pimcore\Translation\Translator;

class SuccessMessageData implements DataInterface
{
    const IDENTIFIER_STRING = 'string';

    const IDENTIFIER_SNIPPET = 'snippet';

    const IDENTIFIER_REDIRECT = 'redirect';

    /**
     * @var HrefLocaleMapper
     */
    protected $hrefLocaleMapper;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var array
     */
    private $data = [];

    /**
     * SuccessMessageData constructor.
     *
     * @param HrefLocaleMapper $hrefLocaleMapper
     * @param Translator       $translator
     */
    public function __construct(
        HrefLocaleMapper $hrefLocaleMapper,
        Translator $translator
    ) {
        $this->hrefLocaleMapper = $hrefLocaleMapper;
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function hasData()
    {
        return !empty($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $locale
     * @return null|Document|Snippet|string
     */
    public function getIdentifiedData($locale)
    {
        if ($this->isStringSuccess()) {
            return $this->getString($locale);
        } elseif ($this->isSnippet()) {
            return $this->getSnippet($locale);
        } elseif ($this->isDocumentRedirect()) {
            return $this->getDocument($locale);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isStringSuccess()
    {
        return isset($this->data[self::IDENTIFIER_STRING]) && !empty($this->data[self::IDENTIFIER_STRING]);
    }

    /**
     * @param $locale
     * @return null|string
     */
    public function getString($locale)
    {
        return $this->isStringSuccess() ? $this->translator->trans((string)$this->data[self::IDENTIFIER_STRING], [], null, $locale) : null;
    }

    /**
     * @return bool
     */
    public function isSnippet()
    {
        return isset($this->data[self::IDENTIFIER_SNIPPET]) && !empty($this->data[self::IDENTIFIER_SNIPPET]);
    }

    /**
     * @param $locale
     * @return Snippet|null
     */
    public function getSnippet($locale)
    {
        if (!$this->isSnippet()) {
            return null;
        }

        $snippetId = $this->hrefLocaleMapper->map($locale, $this->data[self::IDENTIFIER_SNIPPET]);

        if (is_numeric($snippetId)) {
            return Snippet::getById($snippetId);
        }

        return null;

    }

    /**
     * @return bool
     */
    public function isDocumentRedirect()
    {
        return isset($this->data[self::IDENTIFIER_REDIRECT]) && !empty($this->data[self::IDENTIFIER_REDIRECT]);
    }

    /**
     * @param $locale
     * @return Document|null
     */
    public function getDocument($locale)
    {
        if (!$this->isDocumentRedirect()) {
            return null;
        }

        $documentId = $this->hrefLocaleMapper->map($locale, $this->data[self::IDENTIFIER_REDIRECT]);

        if (is_numeric($documentId)) {
            return Document::getById($documentId);
        }

        return null;
    }
}