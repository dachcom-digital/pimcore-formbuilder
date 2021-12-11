<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface ApiProviderInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array
     */
    public function getProviderConfigurationFields(FormDefinitionInterface $formDefinition);

    /**
     * @param FormDefinitionInterface $formDefinition
     * @param array                   $providerConfiguration
     *
     * @return array
     */
    public function getPredefinedApiFields(FormDefinitionInterface $formDefinition, array $providerConfiguration);

    /**
     * @param ApiData $apiData
     */
    public function process(ApiData $apiData);
}
