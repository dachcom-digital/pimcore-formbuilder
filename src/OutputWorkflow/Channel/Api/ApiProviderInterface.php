<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface ApiProviderInterface
{
    public function getName(): string;

    public function getProviderConfigurationFields(FormDefinitionInterface $formDefinition): array;

    public function getPredefinedApiFields(FormDefinitionInterface $formDefinition, array $providerConfiguration): array;

    public function process(ApiData $apiData): void;
}
