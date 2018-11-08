<?php

namespace FormBuilderBundle\Resolver;

use Pimcore\Model\Document;

class FormOptionsResolver
{
    /**
     * @var null
     */
    protected $formId = null;

    /**
     * @var null
     */
    protected $mainLayout = null;

    /**
     * @var null
     */
    protected $formTemplate = null;

    /**
     * @var null
     */
    protected $formBlockTemplate = null;

    /**
     * @var string
     */
    protected $preset = 'custom';

    /**
     * @var bool
     */
    protected $sendCopy = false;

    /**
     * @var null
     */
    protected $mailTemplate = null;

    /**
     * @var null
     */
    protected $mailCopyTemplate = null;

    /**
     * @param $formId
     */
    public function setFormId($formId)
    {
        if (is_numeric($formId)) {
            $this->formId = (int)$formId;
        }
    }

    /**
     * @return null|int
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param $mainLayout
     */
    public function setMainLayout($mainLayout)
    {
        $this->mainLayout = $mainLayout;
    }

    /**
     * @return null
     */
    public function getMainLayout()
    {
        return $this->mainLayout;
    }

    /**
     * @param $preset
     */
    public function setFormPreset($preset = null)
    {
        if (!empty($preset) && !is_null($preset)) {
            $this->preset = $preset;
        }
    }

    /**
     * @return string
     */
    public function getFormPreset()
    {
        return $this->preset;
    }

    /**
     * @param $formTemplate
     */
    public function setFormTemplate($formTemplate)
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
    public function getFormTemplate()
    {
        return '@FormBuilder/Form/Theme/' . $this->formTemplate;
    }

    /**
     * @return null|string
     */
    public function getFormTemplateName()
    {
        if (empty($this->formTemplate)) {
            return 'form_div_layout';
        }

        return substr($this->formTemplate, 0, strpos($this->formTemplate, '.'));
    }

    /**
     * @return string
     */
    public function getFormBlockTemplate()
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
    public function setSendCopy($sendCopy)
    {
        $this->sendCopy = $sendCopy;
    }

    /**
     * @return bool
     */
    public function getSendCopy()
    {
        if ($this->sendCopy === true && is_null($this->getCopyMailTemplateId())) {
            $this->sendCopy = false;
        }

        return $this->sendCopy;
    }

    /**
     * @param $mailTemplate
     */
    public function setMailTemplate($mailTemplate)
    {
        if (is_numeric($mailTemplate)) {
            $mailTemplate = Document\Email::getById($mailTemplate);
        }

        if ($mailTemplate instanceof Document\Email) {
            $mailTemplate = $this->checkI18nPath($mailTemplate);
            $this->mailTemplate = $mailTemplate;
        }
    }

    /**
     * @return null|Document\Email
     */
    public function getMailTemplate()
    {
        return $this->mailTemplate;
    }

    /**
     * @return int|null
     */
    public function getMailTemplateId()
    {
        if ($this->mailTemplate instanceof Document\Email) {
            return $this->mailTemplate->getId();
        }

        return null;
    }

    /**
     * @param $mailTemplate
     */
    public function setCopyMailTemplate($mailTemplate)
    {
        if (is_numeric($mailTemplate)) {
            $mailTemplate = Document\Email::getById($mailTemplate);
        }

        if ($mailTemplate instanceof Document\Email) {
            $mailTemplate = $this->checkI18nPath($mailTemplate);
            $this->mailCopyTemplate = $mailTemplate;
        }
    }

    /**
     * @return null|Document\Email
     */
    public function getCopyMailTemplate()
    {
        return $this->mailCopyTemplate;
    }

    /**
     * @return int|null
     */
    public function getCopyMailTemplateId()
    {
        if ($this->mailCopyTemplate instanceof Document\Email) {
            return $this->mailCopyTemplate->getId();
        }

        return null;
    }

    /**
     * Detect if email is in right i18n context.
     * This method only works if you have enabled the i18n bundle.
     *
     * @see https://github.com/dachcom-digital/pimcore-i18n/blob/master/docs/90_InternalLinkRewriter.md#internal-link-rewriter
     *
     * @param Document\Email $mailTemplate
     * @return Document\Email
     */
    private function checkI18nPath(Document\Email $mailTemplate)
    {
        $i18nFullPath = $mailTemplate->getFullPath();
        $fullPath = $mailTemplate->getPath() . $mailTemplate->getKey();

        if ($i18nFullPath === $fullPath) {
            return $mailTemplate;
        }

        $realFullPath = str_replace($fullPath, '', $mailTemplate->getRealFullPath());

        $mailPath = $mailTemplate->getFullPath();
        $i18nRealFullPath = $realFullPath . $mailPath;

        if (Document\Service::pathExists($i18nRealFullPath)) {
            $mailTemplate = Document\Email::getByPath($i18nRealFullPath);
        }

        return $mailTemplate;

    }
}