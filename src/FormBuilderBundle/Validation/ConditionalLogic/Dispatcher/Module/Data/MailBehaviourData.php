<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

use FormBuilderBundle\Tool\HrefLocaleMapper;

class MailBehaviourData implements DataInterface
{
    const IDENTIFIER_MAIL_TEMPLATE = 'mailTemplate';

    const IDENTIFIER_RECIPIENT = 'recipient';

    /**
     * @var HrefLocaleMapper
     */
    protected $hrefLocaleMapper;

    /**
     * @var array
     */
    private $data = [];

    /**
     * DynamicChoiceType constructor.
     *
     * @param HrefLocaleMapper $hrefLocaleMapper
     */
    public function __construct(HrefLocaleMapper $hrefLocaleMapper)
    {
        $this->hrefLocaleMapper = $hrefLocaleMapper;
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
     * @return bool
     */
    public function hasRecipient()
    {
        return isset($this->data[self::IDENTIFIER_RECIPIENT]) && !empty($this->data[self::IDENTIFIER_RECIPIENT]);
    }

    /**
     * @return null|string
     */
    public function getRecipient()
    {
        return $this->hasRecipient() ? $this->data[self::IDENTIFIER_RECIPIENT] : null;
    }

    /**
     * @return bool
     */
    public function hasMailTemplate()
    {
        return isset($this->data[self::IDENTIFIER_MAIL_TEMPLATE]) && !empty($this->data[self::IDENTIFIER_MAIL_TEMPLATE]);
    }

    /**
     * @param string $locale
     *
     * @return null|int
     */
    public function getMailTemplateId($locale)
    {
        if (!$this->hasMailTemplate()) {
            return null;
        }

        return (int) $this->hrefLocaleMapper->map($locale, $this->data[self::IDENTIFIER_MAIL_TEMPLATE]);
    }
}
