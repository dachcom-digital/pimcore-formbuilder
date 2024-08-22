<?php

namespace FormBuilderBundle\Form\RuntimeData;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Registry\RuntimeDataProviderRegistry;

class FormRuntimeDataAllocator implements FormRuntimeDataAllocatorInterface
{
    public function __construct(protected RuntimeDataProviderRegistry $runtimeDataProviderRegistry)
    {
    }

    public function allocate(FormDefinitionInterface $formDefinition, array $systemRuntimeData, bool $headless): RuntimeDataCollector
    {
        $dataCollector = new RuntimeDataCollector();

        // add all system runtime data first
        foreach ($systemRuntimeData as $systemRuntimeDataId => $systemRuntimeDataBlock) {
            $dataCollector->add($systemRuntimeDataId, $systemRuntimeDataBlock);
        }

        /** @var RuntimeDataProviderInterface $dataProvider */
        foreach ($this->runtimeDataProviderRegistry->getAll() as $dataProvider) {

            if ($headless === true && !$dataProvider instanceof HeadlessAwareRuntimeDataProviderInterface) {
                continue;
            }

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
