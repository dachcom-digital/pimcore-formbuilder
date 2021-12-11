<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use Symfony\Component\Form\FormInterface;

class ApiData
{
    protected array $apiNodes;
    protected ?array $providerConfiguration;
    protected string $locale;
    protected array $formRuntimeData;
    protected FormInterface $form;

    public function __construct(
        array $apiNodes,
        ?array $providerConfiguration,
        string $locale,
        array $formRuntimeData,
        FormInterface $form
    ) {
        $this->apiNodes = $apiNodes;
        $this->providerConfiguration = $providerConfiguration;
        $this->locale = $locale;
        $this->formRuntimeData = $formRuntimeData;
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getFormRuntimeData()
    {
        return $this->formRuntimeData;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return null|array
     */
    public function getProviderConfiguration()
    {
        return $this->providerConfiguration;
    }

    /**
     * @param string $node
     *
     * @return mixed|null
     */
    public function getProviderConfigurationNode(string $node)
    {
        if (!is_array($this->providerConfiguration)) {
            return null;
        }

        return $this->providerConfiguration[$node] ?? null;
    }

    /**
     * @param string $node
     *
     * @return bool
     */
    public function hasProviderConfigurationNode(string $node)
    {
        if (!is_array($this->providerConfiguration)) {
            return false;
        }

        return isset($this->providerConfiguration[$node]);
    }

    /**
     * @param array $apiNodes
     */
    public function setApiNodes(array $apiNodes)
    {
        $this->apiNodes = $apiNodes;
    }

    /**
     * @return array
     */
    public function getApiNodes()
    {
        return $this->apiNodes;
    }

    /**
     * @param string $node
     *
     * @return mixed|null
     */
    public function getApiNode(string $node)
    {
        return $this->apiNodes[$node] ?? null;
    }

    /**
     * @param string $node
     *
     * @return bool
     */
    public function hasApiNode(string $node)
    {
        return isset($this->apiNodes[$node]);
    }
}
