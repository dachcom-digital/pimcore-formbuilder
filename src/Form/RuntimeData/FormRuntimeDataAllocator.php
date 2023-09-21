<?php

namespace FormBuilderBundle\Form\RuntimeData;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Registry\RuntimeDataProviderRegistry;

class FormRuntimeDataAllocator implements FormRuntimeDataAllocatorInterface
{
    public function __construct(protected RuntimeDataProviderRegistry $runtimeDataProviderRegistry)
    {
    }

    public function allocate(FormDefinitionInterface $formDefinition, array $systemRuntimeData): RuntimeDataCollector
    {
        $dataCollector = new RuntimeDataCollector();

        // add all system runtime data first
        foreach ($systemRuntimeData as $systemRuntimeDataId => $systemRuntimeDataBlock) {
            $dataCollector->add($systemRuntimeDataId, $systemRuntimeDataBlock);
        }

        foreach ($this->runtimeDataProviderRegistry->getAll() as $dataProviderIdentifier => $dataProvider) {
            if (!$dataProvider->hasRuntimeData($formDefinition)) {
                continue;
            }

            $providerDataId = $dataProvider->getRuntimeDataId();
            $providerData = $dataProvider->getRuntimeData($formDefinition);

            $dataCollector->add($providerDataId, $providerData);
        }

        return $dataCollector;
    }
}
