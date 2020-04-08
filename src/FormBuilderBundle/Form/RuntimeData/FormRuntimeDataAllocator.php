<?php

namespace FormBuilderBundle\Form\RuntimeData;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Registry\RuntimeDataProviderRegistry;

class FormRuntimeDataAllocator implements FormRuntimeDataAllocatorInterface
{
    /**
     * @var RuntimeDataProviderRegistry
     */
    protected $runtimeDataProviderRegistry;

    /**
     * @param RuntimeDataProviderRegistry $runtimeDataProviderRegistry
     */
    public function __construct(RuntimeDataProviderRegistry $runtimeDataProviderRegistry)
    {
        $this->runtimeDataProviderRegistry = $runtimeDataProviderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function allocate(FormDefinitionInterface $formDefinition, array $systemRuntimeData)
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
