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
}
