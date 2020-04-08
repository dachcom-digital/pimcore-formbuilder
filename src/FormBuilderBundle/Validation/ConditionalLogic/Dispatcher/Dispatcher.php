<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher;

use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Registry\DispatcherRegistry;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Processor\ConditionalLogicProcessor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Dispatcher
{
    /**
     * @var ConditionalLogicProcessor
     */
    protected $conditionalLogicProcessor;

    /**
     * @var OptionsResolver
     */
    protected $dispatcherOptions = null;

    /**
     * @var DispatcherRegistry
     */
    protected $dispatcherRegistry;

    /**
     * @var array
     */
    protected $optionsResolver = [];

    /**
     * @param ConditionalLogicProcessor $conditionalLogicProcessor
     * @param DispatcherRegistry        $dispatcherRegistry
     */
    public function __construct(DispatcherRegistry $dispatcherRegistry, ConditionalLogicProcessor $conditionalLogicProcessor)
    {
        $this->dispatcherRegistry = $dispatcherRegistry;
        $this->conditionalLogicProcessor = $conditionalLogicProcessor;
    }

    /**
     * @param string $dispatcherModule
     * @param array  $options
     * @param array  $moduleOptions
     *
     * @return DataInterface
     *
     * @throws \Exception
     */
    public function runFieldDispatcher($dispatcherModule, $options, $moduleOptions = [])
    {
        $dispatcherOptions = $this->createOptionsResolver('field');
        $dispatcherOptions->resolve($options);

        $conditionActions = $this->conditionalLogicProcessor->process($options['formData'], $options['conditionalLogic'], $options['field']);
        $moduleOptions['appliedConditions'] = $conditionActions;

        return $this->run($dispatcherModule, $options, $moduleOptions);
    }

    /**
     * @param string $dispatcherModule
     * @param array  $options
     * @param array  $moduleOptions
     *
     * @return DataInterface
     *
     * @throws \Exception
     */
    public function runFormDispatcher($dispatcherModule, $options, $moduleOptions = [])
    {
        $dispatcherOptions = $this->createOptionsResolver('form');
        $dispatcherOptions->resolve($options);

        $conditionActions = $this->conditionalLogicProcessor->process($options['formData'], $options['conditionalLogic']);
        $moduleOptions['appliedConditions'] = $conditionActions;

        return $this->run($dispatcherModule, $options, $moduleOptions);
    }

    /**
     * @param string $dispatcherModule
     * @param array  $options
     * @param array  $moduleOptions
     *
     * @return DataInterface
     *
     * @throws \Exception
     */
    private function run($dispatcherModule, $options, $moduleOptions)
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

    /**
     * @param string $type
     *
     * @return OptionsResolver
     */
    private function createOptionsResolver($type = 'field')
    {
        $dispatcherOptions = new OptionsResolver();
        $dispatcherOptions->setDefaults([
            'formData'         => [],
            'conditionalLogic' => []
        ]);

        $dispatcherOptions->setRequired(['formData', 'conditionalLogic']);

        if ($type === 'field') {
            $dispatcherOptions->setDefaults(['field' => null]);
            $dispatcherOptions->setRequired(['field']);
            $dispatcherOptions->setAllowedTypes('field', FormFieldDefinitionInterface::class);
        }

        return $dispatcherOptions;
    }
}
