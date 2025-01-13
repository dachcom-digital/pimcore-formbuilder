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

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Registry\DispatcherRegistry;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Processor\ConditionalLogicProcessor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Dispatcher
{
    protected array $optionsResolver = [];

    public function __construct(
        protected DispatcherRegistry $dispatcherRegistry,
        protected ConditionalLogicProcessor $conditionalLogicProcessor
    ) {
    }

    /**
     * @throws \Exception
     */
    public function runFieldDispatcher(string $dispatcherModule, array $options, array $moduleOptions = []): DataInterface
    {
        $dispatcherOptions = $this->createOptionsResolver('field');

        $conditionActions = $this->conditionalLogicProcessor->process($dispatcherOptions->resolve($options));
        $moduleOptions['appliedConditions'] = $conditionActions;

        return $this->run($dispatcherModule, $options, $moduleOptions);
    }

    /**
     * @throws \Exception
     */
    public function runFormDispatcher(string $dispatcherModule, array $options, array $moduleOptions = []): DataInterface
    {
        $dispatcherOptions = $this->createOptionsResolver('form');

        $conditionActions = $this->conditionalLogicProcessor->process($dispatcherOptions->resolve($options));
        $moduleOptions['appliedConditions'] = $conditionActions;

        return $this->run($dispatcherModule, $options, $moduleOptions);
    }

    /**
     * @throws \Exception
     */
    private function run(string $dispatcherModule, array $options, array $moduleOptions): DataInterface
    {
        if (isset($this->optionsResolver[$dispatcherModule])) {
            $optionsResolver = $this->optionsResolver[$dispatcherModule];
        } else {
            $optionsResolver = new OptionsResolver();
            $this->optionsResolver[$dispatcherModule] = $optionsResolver;
        }

        $dispatcherModuleClass = $this->dispatcherRegistry->get($dispatcherModule);
        $dispatcherModuleClass->configureOptions($optionsResolver);

        //pass available dispatcher option to module if available
        foreach ($optionsResolver->getDefinedOptions() as $optionName) {
            if (isset($options[$optionName])) {
                $moduleOptions[$optionName] = $options[$optionName];
            }
        }

        $moduleOptions = $optionsResolver->resolve($moduleOptions);

        return $dispatcherModuleClass->apply($moduleOptions);
    }

    private function createOptionsResolver(string $type = 'field'): OptionsResolver
    {
        $dispatcherOptions = new OptionsResolver();
        $dispatcherOptions->setDefaults([
            'formData'           => [],
            'formRuntimeOptions' => [],
            'conditionalLogic'   => []
        ]);

        $dispatcherOptions->setRequired(['formData', 'conditionalLogic', 'formRuntimeOptions']);

        if ($type === 'field') {
            $dispatcherOptions->setDefaults(['field' => null]);
            $dispatcherOptions->setRequired(['field']);
            $dispatcherOptions->setAllowedTypes('field', FieldDefinitionInterface::class);
        }

        return $dispatcherOptions;
    }
}
