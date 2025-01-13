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
