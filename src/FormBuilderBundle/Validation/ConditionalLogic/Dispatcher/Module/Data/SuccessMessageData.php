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

    const IDENTIFIER_REDIRECT_EXTERNAL = 'redirect_external';

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
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasData()
    {
        return !empty($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $locale
     *
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
        } elseif ($this->isExternalRedirect()) {
            return $this->getExternalRedirect();
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
     * @param string $locale
     *
     * @return null|string
     */
    public function getString($locale)
    {
        return $this->isStringSuccess() ? $this->translator->trans((string) $this->data[self::IDENTIFIER_STRING], [], null, $locale) : null;
    }

    /**
     * @return bool
     */
    public function isSnippet()
    {
        return isset($this->data[self::IDENTIFIER_SNIPPET]) && !empty($this->data[self::IDENTIFIER_SNIPPET]);
    }

    /**
     * @param string $locale
     *
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
     * @param string $locale
     *
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

    /**
     * @return bool
     */
    public function isExternalRedirect()
    {
        return isset($this->data[self::IDENTIFIER_REDIRECT_EXTERNAL])
            && !empty($this->data[self::IDENTIFIER_REDIRECT_EXTERNAL])
            && substr($this->data[self::IDENTIFIER_REDIRECT_EXTERNAL], 0, 4) === 'http';
    }

    /**
     * @return string|null
     */
    public function getExternalRedirect()
    {
        if (!$this->isExternalRedirect()) {
            return null;
        }

        return $this->data[self::IDENTIFIER_REDIRECT_EXTERNAL];
    }

    /**
     * @return bool
     */
    public function hasFlashMessage()
    {
        return $this->isDocumentRedirect() && isset($this->data['flashMessage']) && !empty($this->data['flashMessage']);
    }

    /**
     * @param string $locale
     *
     * @return null|string
     */
    public function getFlashMessage($locale)
    {
        if (!$this->hasFlashMessage()) {
            return null;
        }

        return $this->hasFlashMessage() ? $this->translator->trans((string) $this->data['flashMessage'], [], null, $locale) : null;
    }
}
