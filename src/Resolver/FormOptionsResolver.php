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

namespace FormBuilderBundle\Resolver;

class FormOptionsResolver
{
    protected ?int $formId = null;
    protected ?string $mainLayout = null;
    protected ?string $formTemplate = null;
    protected ?string $formBlockTemplate = null;
    protected int|string|null $outputWorkflow = null;
    protected string $preset = 'custom';
    protected bool $useCsrfProtection = true;
    protected array $customOptions = [];
    protected array $formAttributes = [];

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

    public function useCsrfProtection(): bool
    {
        return $this->useCsrfProtection;
    }

    public function setUseCsrfProtection(bool $useCsrfProtection): void
    {
        $this->useCsrfProtection = $useCsrfProtection;
    }

    public function setCustomOptions(array $customOptions = []): void
    {
        $this->customOptions = $customOptions;
    }

    public function getCustomOptions(): array
    {
        return $this->customOptions;
    }

    public function getFormAttributes(): array
    {
        return $this->formAttributes;
    }

    public function setFormAttributes(array $formAttributes): void
    {
        $this->formAttributes = $formAttributes;
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
        return sprintf('@FormBuilder/form/theme/%s', $this->formTemplate);
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
        return sprintf('@FormBuilder/form/theme/macro/%s', $this->formBlockTemplate);
    }

    public function getFormLayout(): string
    {
        $path = '@FormBuilder/form/%s.html.twig';
        $template = $this->getFormPreset() === 'custom' ? 'default' : sprintf('presets/%s', $this->getFormPreset());

        return sprintf($path, $template);
    }
}
