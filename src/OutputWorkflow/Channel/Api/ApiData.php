<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use Symfony\Component\Form\FormInterface;

class ApiData
{
    protected string $apiProviderName;
    protected array $apiNodes;
    protected ?array $providerConfiguration;
    protected string $locale;
    protected array $formRuntimeData;
    protected FormInterface $form;

    public function __construct(
        string $apiProviderName,
        array $apiNodes,
        ?array $providerConfiguration,
        string $locale,
        array $formRuntimeData,
        FormInterface $form
    ) {
        $this->apiProviderName = $apiProviderName;
        $this->apiNodes = $apiNodes;
        $this->providerConfiguration = $providerConfiguration;
        $this->locale = $locale;
        $this->formRuntimeData = $formRuntimeData;
        $this->form = $form;
    }

    public function getApiProviderName(): string
    {
        return $this->apiProviderName;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getFormRuntimeData(): array
    {
        return $this->formRuntimeData;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getProviderConfiguration(): ?array
    {
        return $this->providerConfiguration;
    }

    public function getProviderConfigurationNode(string $node): mixed
    {
        if (!is_array($this->providerConfiguration)) {
            return null;
        }

        return $this->providerConfiguration[$node] ?? null;
    }

    public function hasProviderConfigurationNode(string $node): bool
    {
        if (!is_array($this->providerConfiguration)) {
            return false;
        }

        return isset($this->providerConfiguration[$node]);
    }

    public function setApiNodes(array $apiNodes): void
    {
        $this->apiNodes = $apiNodes;
    }

    public function getApiNodes(): array
    {
        return $this->apiNodes;
    }

    public function getApiNode(string $node): mixed
    {
        return $this->apiNodes[$node] ?? null;
    }

    public function hasApiNode(string $node): bool
    {
        return isset($this->apiNodes[$node]);
    }
}
