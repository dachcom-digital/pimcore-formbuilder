<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\ConstraintsData;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Factory\DataFactory;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class Constraints implements ModuleInterface
{
    protected TranslatorInterface $translator;
    protected DataFactory $dataFactory;

    protected array $formData = [];
    protected ?FormFieldDefinitionInterface $field = null;
    protected array $availableConstraints = [];
    protected array $appliedConditions = [];

    public function __construct(
        TranslatorInterface $translator,
        DataFactory $dataFactory
    ) {
        $this->translator = $translator;
        $this->dataFactory = $dataFactory;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'formData'             => [],
            'field'                => null,
            'availableConstraints' => [],
            'appliedConditions'    => []
        ]);

        $resolver->setRequired(['formData', 'field', 'availableConstraints', 'appliedConditions']);
        $resolver->setAllowedTypes('field', FormFieldDefinitionInterface::class);
        $resolver->setAllowedTypes('formData', ['array', 'null']);
        $resolver->setAllowedTypes('availableConstraints', 'array');
        $resolver->setAllowedTypes('appliedConditions', 'array');
    }

    public function apply(array $options): DataInterface
    {
        $this->formData = $options['formData'];
        $this->field = $options['field'];
        $this->availableConstraints = $options['availableConstraints'];
        $this->appliedConditions = $options['appliedConditions'];

        //add defaults
        $fieldConstraints = $this->field->getConstraints();
        $validConstraints = $this->checkConditionalLogicConstraints($fieldConstraints);

        $completeConstraintData = $this->appendConstraintsData($validConstraints);

        $returnContainer = $this->dataFactory->generate(ConstraintsData::class);
        $returnContainer->setData($completeConstraintData);

        return $returnContainer;
    }

    private function checkConditionalLogicConstraints(array $defaultFieldConstraints): array
    {
        if (empty($this->appliedConditions)) {
            return $defaultFieldConstraints;
        }

        /** @var ReturnStackInterface $returnStack */
        foreach ($this->appliedConditions as $ruleId => $returnStack) {
            if (!$returnStack instanceof FieldReturnStack || !in_array($returnStack->getActionType(), [
                'addConstraints',
                'removeConstraints'
            ])) {
                continue;
            }

            if ($returnStack->getActionType() === 'addConstraints') {
                foreach ($returnStack->getData() as $constraint) {
                    $defaultFieldConstraints[] = ['type' => $constraint];
                }
            } elseif ($returnStack->getActionType() === 'removeConstraints') {
                if ($returnStack->getData() === 'all') {
                    $defaultFieldConstraints = [];
                } else {
                    foreach ($returnStack->getData() as $constraint) {
                        $defaultFieldConstraints = array_filter($defaultFieldConstraints, function ($val) use ($constraint) {
                            return $val['type'] !== $constraint;
                        });
                    }
                }
            }
        }

        $tempArr = array_unique(array_column($defaultFieldConstraints, 'type'));
        array_intersect_key($defaultFieldConstraints, $tempArr);

        return $defaultFieldConstraints;
    }

    private function appendConstraintsData(array $constraints): array
    {
        $constraintData = [];
        foreach ($constraints as $constraint) {
            $constraintType = $constraint['type'];
            $constraintConfig = isset($constraint['config']) ? $constraint['config'] : [];

            if (!isset($this->availableConstraints[$constraintType])) {
                continue;
            }

            $constraintInfo = $this->availableConstraints[$constraintType];

            //translate custom message.
            if (isset($constraintConfig['message']) && !empty($constraintConfig['message'])) {
                $configKey = array_search('message', array_column($constraintInfo['config'], 'name'));
                if ($configKey !== false) {
                    $defaultMessage = $constraintInfo['config'][$configKey]['defaultValue'];
                    if (!empty($defaultMessage) && !empty($constraintConfig['message']) && $defaultMessage !== $constraintConfig['message']) {
                        $constraintConfig['message'] = $this->translator->trans($constraintConfig['message']);
                    }
                }
            }

            $class = $this->availableConstraints[$constraintType]['class'];
            $constraintData[] = new $class($constraintConfig);
        }

        return $constraintData;
    }
}
