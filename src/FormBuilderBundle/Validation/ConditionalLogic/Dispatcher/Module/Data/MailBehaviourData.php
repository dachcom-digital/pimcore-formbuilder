<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

class MailBehaviourData implements DataInterface
{
    const IDENTIFIER_MAIL_TEMPLATE = 'mailTemplate';

    const IDENTIFIER_RECIPIENT = 'recipient';

    /**
     * @var array
     */
    private $data = [];

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
     * @return null|string
     */
    public function getMailTemplateId()
    {
        return $this->hasMailTemplate() ? (int)$this->data[self::IDENTIFIER_MAIL_TEMPLATE] : null;
    }
}