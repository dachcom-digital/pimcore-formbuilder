<?php

namespace FormBuilderBundle\Resolver;

use Pimcore\Model\Document\Email;

class FormOptionsResolver
{
    /**
     * @var null
     */
    protected $formId = NULL;

    /**
     * @var null
     */
    protected $mainLayout = NULL;

    /**
     * @var null
     */
    protected $formTemplate = NULL;

    /**
     * @var null
     */
    protected $formBlockTemplate = NULL;

    /**
     * @var string
     */
    protected $preset = 'custom';

    /**
     * @var bool
     */
    protected $sendCopy = FALSE;

    /**
     * @var null
     */
    protected $mailTemplate = NULL;

    /**
     * @var null
     */
    protected $mailCopyTemplate = NULL;

    /**
     * @param $formId
     */
    function setFormId($formId)
    {
        if (is_numeric($formId)) {
            $this->formId = $formId;
        }
    }

    /**
     * @return null|int
     */
    function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param $mainLayout
     */
    function setMainLayout($mainLayout)
    {
        $this->mainLayout = $mainLayout;
    }

    /**
     * @return null
     */
    function getMainLayout()
    {
        return $this->mainLayout;
    }

    /**
     * @param $preset
     */
    function setFormPreset($preset = NULL)
    {
        if (!empty($preset) && !is_null($preset)) {
            $this->preset = $preset;
        }
    }

    /**
     * @return string
     */
    function getFormPreset()
    {
        return $this->preset;
    }

    /**
     * @param $formTemplate
     */
    function setFormTemplate($formTemplate)
    {
        if (empty($formTemplate)) {
            $formTemplate = 'form_div_layout.html.twig';
        }

        $this->formTemplate = $formTemplate;
        $this->formBlockTemplate = $formTemplate;
    }

    /**
     * @return string
     */
    function getFormTemplate()
    {
        return '@FormBuilder/Form/Theme/' . $this->formTemplate;
    }

    /**
     * @return null|string
     */
    function getFormTemplateName()
    {
        if (empty($this->formTemplate)) {
            return 'form_div_layout';
        }

        return substr($this->formTemplate, 0, strpos($this->formTemplate, '.'));
    }

    /**
     * @return string
     */
    function getFormBlockTemplate()
    {
        return '@FormBuilder/Form/Theme/Macro/' . $this->formBlockTemplate;
    }

    /**
     * @return string
     */
    public function getFormLayout()
    {
        $path = '@FormBuilder/Form/%s.html.twig';
        $template = $this->getFormPreset() === 'custom' ? 'default' : 'Presets/' . $this->getFormPreset();
        return sprintf($path, $template);
    }

    /**
     * @param $sendCopy
     */
    function setSendCopy($sendCopy)
    {
        $this->sendCopy = $sendCopy;
    }

    /**
     * @return bool
     */
    function getSendCopy()
    {
        if ($this->sendCopy === TRUE && is_null($this->getCopyMailTemplateId())) {
            $this->sendCopy = FALSE;
        }

        return $this->sendCopy;
    }

    /**
     * @param $mailTemplate
     */
    function setMailTemplate($mailTemplate)
    {
        if(is_numeric($mailTemplate)) {
            $mailTemplate = Email::getById($mailTemplate);
        }

        if ($mailTemplate instanceof Email) {
            $this->mailTemplate = $mailTemplate;
        }
    }

    /**
     * @return null|Email
     */
    function getMailTemplate()
    {
        return $this->mailTemplate;
    }

    /**
     * @return int|null
     */
    function getMailTemplateId()
    {
        if ($this->mailTemplate instanceof Email) {
            return $this->mailTemplate->getId();
        }

        return NULL;
    }

    /**
     * @param $mailTemplate
     */
    function setCopyMailTemplate($mailTemplate)
    {
        if(is_numeric($mailTemplate)) {
            $mailTemplate = Email::getById($mailTemplate);
        }

        if ($mailTemplate instanceof Email) {
            $this->mailCopyTemplate = $mailTemplate;
        }
    }

    /**
     * @return null|Email
     */
    function getCopyMailTemplate()
    {
        return $this->mailCopyTemplate;
    }

    /**
     * @return int|null
     */
    function getCopyMailTemplateId()
    {
        if ($this->mailCopyTemplate instanceof Email) {
            return $this->mailCopyTemplate->getId();
        }

        return NULL;
    }
}