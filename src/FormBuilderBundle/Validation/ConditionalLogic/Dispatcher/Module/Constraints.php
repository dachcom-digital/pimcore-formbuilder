<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\ConstraintsData;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Factory\DataFactory;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class Constraints implements ModuleInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DataFactory
     */
    protected $dataFactory;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var array
     */
    protected $formData;

    /**
     * @var FormFieldInterface
     */
    protected $field;

    /**
     * @var array
     */
    protected $availableConstraints;

    /**
     * @var array
     */
    protected $appliedConditions;

    /**
     * @param TranslatorInterface $translator
     * @param DataFactory         $dataFactory
     */
    public function __construct(
        TranslatorInterface $translator,
        DataFactory $dataFactory
    ) {
        $this->translator = $translator;
        $this->dataFactory = $dataFactory;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'formData'             => [],
            'field'                => null,
            'availableConstraints' => [],
            'appliedConditions'    => []
        ]);

        $resolver->setRequired(['formData', 'field', 'availableConstraints', 'appliedConditions']);
        $resolver->setAllowedTypes('field', FormFieldInterface::class);
        $resolver->setAllowedTypes('formData', ['array', 'null']);
        $resolver->setAllowedTypes('availableConstraints', 'array');
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

    /**
     * Constraints from current conditional logic.
     *
     * @param array $defaultFieldConstraints
     *
     * @return array
     */
    private function checkConditionalLogicConstraints($defaultFieldConstraints)
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

    /**
     * @param array $constraints
     *
     * @return array
     */
    private function appendConstraintsData($constraints)
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
