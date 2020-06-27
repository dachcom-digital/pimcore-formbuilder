<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\FormTypeClassesData;
use FormBuilderBundle\Validation\ConditionalLogic\Factory\DataFactory;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeClasses implements ModuleInterface
{
    /**
     * @var DataFactory
     */
    protected $dataFactory;

    /**
     * @var array
     */
    protected $formData;

    /**
     * @var FieldDefinitionInterface
     */
    protected $field;

    /**
     * @var array
     */
    protected $appliedConditions;

    /**
     * @param DataFactory $dataFactory
     */
    public function __construct(DataFactory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'formData'          => [],
            'field'             => null,
            'appliedConditions' => []
        ]);

        $resolver->setRequired(['formData', 'field', 'appliedConditions']);
        $resolver->setAllowedTypes('field', FieldDefinitionInterface::class);
        $resolver->setAllowedTypes('formData', ['array', 'null']);
        $resolver->setAllowedTypes('appliedConditions', 'array');
    }

    /**
     * @param array $options
     *
     * @return DataInterface
     */
    public function apply($options)
    {
        $this->formData = $options['formData'];
        $this->field = $options['field'];
        $this->appliedConditions = $options['appliedConditions'];

        return $this->checkConditionData();
    }

    /**
     * @return DataInterface
     */
    private function checkConditionData()
    {
        $returnContainer = $this->dataFactory->generate(FormTypeClassesData::class);

        if (empty($this->appliedConditions)) {
            return $returnContainer;
        }

        $classes = [];

        /** @var ReturnStackInterface $returnStack */
        foreach ($this->appliedConditions as $ruleId => $returnStack) {
            if (!$returnStack instanceof FieldReturnStack) {
                continue;
            }

            if (!in_array($returnStack->getActionType(), ['toggleClass', 'toggleElement'])) {
                continue;
            }

            if (empty($returnStack->getData())) {
                continue;
            }

            if ($returnStack->getActionType() === 'toggleClass') {
                $classes[] = $returnStack->getData();
            } elseif ($returnStack->getActionType() === 'toggleElement') {
                $classes[] = $returnStack->getData();
            }
        }

        $returnContainer->setData(array_unique($classes));

        return $returnContainer;
    }
}
