<?php

namespace FormBuilderBundle\Resolver;

use Pimcore\Model\Document;

class FormOptionsResolver
{
    protected ?int $formId = null;
    protected ?string $mainLayout = null;
    protected ?string $formTemplate = null;
    protected ?string $formBlockTemplate = null;
    protected ?int $outputWorkflow = null;
    protected string $preset = 'custom';
    protected array $customOptions = [];
    protected bool $sendCopy = false;
    protected ?Document\Email $mailTemplate = null;
    protected ?Document\Email $mailCopyTemplate = null;

    public function setFormId(?int $formId): void
    {
        if (is_numeric($formId)) {
            $this->formId = (int) $formId;
        }
    }

    public function getFormId(): ?int
    {
        return $this->formId;
    }

    public function setMainLayout(?string $mainLayout): void
    {
        $this->mainLayout = $mainLayout;
    }

    public function getMainLayout(): ?string
    {
        return $this->mainLayout;
    }

    public function setOutputWorkflow(int|string|null $outputWorkflow = null): void
    {
        if (is_numeric($outputWorkflow) || is_string($outputWorkflow)) {
            $this->outputWorkflow = $outputWorkflow;
        }
    }


    public function getOutputWorkflow(): int|string|null
    {
        return $this->outputWorkflow;
    }

    public function setFormPreset(?string $preset = null): void
    {
        if (!empty($preset)) {
            $this->preset = $preset;
        }
    }

    public function getFormPreset(): string
    {
        return $this->preset;
    }

    public function setCustomOptions(array $customOptions = []): void
    {
        $this->customOptions = $customOptions;
    }

    public function getCustomOptions(): array
    {
        return $this->customOptions;
    }

    public function setFormTemplate(string $formTemplate): void
    {
        if (empty($formTemplate)) {
            $formTemplate = 'form_div_layout.html.twig';
        }

        $this->formTemplate = $formTemplate;
        $this->formBlockTemplate = $formTemplate;
    }

    public function getFormTemplate(): string
    {
        return '@FormBuilder/form/theme/' . $this->formTemplate;
    }

    public function getFormTemplateName(): ?string
    {
        if (empty($this->formTemplate)) {
            return 'form_div_layout';
        }

        return substr($this->formTemplate, 0, strpos($this->formTemplate, '.'));
    }

    public function getFormBlockTemplate(): string
    {
        return '@FormBuilder/form/theme/macro/' . $this->formBlockTemplate;
    }

    public function getFormLayout(): string
    {
        $path = '@FormBuilder/form/%s.html.twig';
        $template = $this->getFormPreset() === 'custom' ? 'default' : 'presets/' . $this->getFormPreset();

        return sprintf($path, $template);
    }

    public function setSendCopy(bool $sendCopy): void
    {
        $this->sendCopy = $sendCopy;
    }

    public function getSendCopy(): bool
    {
        if ($this->sendCopy === true && is_null($this->getCopyMailTemplateId())) {
            $this->sendCopy = false;
        }

        return $this->sendCopy;
    }

    public function setMailTemplate(null|int|Document\Email $mailTemplate): void
    {
        if (is_numeric($mailTemplate)) {
            $mailTemplate = Document\Email::getById($mailTemplate);
        }

        if ($mailTemplate instanceof Document\Email) {
            $mailTemplate = $this->checkI18nPath($mailTemplate);
            $this->mailTemplate = $mailTemplate;
        }
    }

    public function getMailTemplate(): ?Document\Email
    {
        return $this->mailTemplate;
    }

    public function getMailTemplateId(): ?int
    {
        if ($this->mailTemplate instanceof Document\Email) {
            return (int) $this->mailTemplate->getId();
        }

        return null;
    }

    public function setCopyMailTemplate(null|int|Document\Email $mailTemplate)
    {
        if (is_numeric($mailTemplate)) {
            $mailTemplate = Document\Email::getById($mailTemplate);
        }

        if ($mailTemplate instanceof Document\Email) {
            $mailTemplate = $this->checkI18nPath($mailTemplate);
            $this->mailCopyTemplate = $mailTemplate;
        }
    }

    public function getCopyMailTemplate(): ?Document\Email
    {
        return $this->mailCopyTemplate;
    }

    public function getCopyMailTemplateId(): ?int
    {
        if ($this->mailCopyTemplate instanceof Document\Email) {
            return (int) $this->mailCopyTemplate->getId();
        }

        return null;
    }

    /**
     * Detect if email is in right i18n context.
     * This method only works if you have enabled the i18n bundle.
     *
     * @see https://github.com/dachcom-digital/pimcore-i18n/blob/master/docs/90_InternalLinkRewriter.md#internal-link-rewriter
     */
    private function checkI18nPath(Document\Email $mailTemplate): Document\Email
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
