<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\SwitchOutputWorkflowData;
use FormBuilderBundle\Validation\ConditionalLogic\Factory\DataFactory;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchOutputWorkflow implements ModuleInterface
{
    protected array $formData;
    protected array $appliedConditions;
    protected array $availableConstraints;

    public function __construct(protected DataFactory $dataFactory)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'formData'             => [],
            'appliedConditions'    => [],
            'availableConstraints' => []
        ]);

        $resolver->setRequired(['formData', 'appliedConditions']);
        $resolver->setAllowedTypes('formData', ['array', 'null']);
        $resolver->setAllowedTypes('appliedConditions', 'array');
    }

    /**
     * {@inheritDoc}
     */
    public function apply(array $options): DataInterface
    {
        $this->formData = $options['formData'];
        $this->availableConstraints = $options['availableConstraints'];
        $this->appliedConditions = $options['appliedConditions'];

        $returnContainer = $this->dataFactory->generate(SwitchOutputWorkflowData::class);

        if (!$returnContainer instanceof DataInterface) {
            throw new \Exception('Could not create SwitchOutputWorkflow container');
        }

        if (empty($this->appliedConditions)) {
            return $returnContainer;
        }

        $switchWorkflowConfig = [];

        /** @var ReturnStackInterface $returnStack */
        foreach ($this->appliedConditions as $returnStack) {

            if (!$returnStack instanceof SimpleReturnStack || $returnStack->getActionType() !== 'switchOutputWorkflow') {
                continue;
            }

            $returnStackData = $returnStack->getData();
            if (empty($returnStackData)) {
                continue;
            }

            if (!isset($returnStackData['workflowId']) || !is_numeric($returnStackData['workflowId'])) {
                continue;
            }

            $switchWorkflowConfig['workflowId'] = (int) $returnStackData['workflowId'];
        }

        $returnContainer->setData($switchWorkflowConfig);

        return $returnContainer;
    }
}
