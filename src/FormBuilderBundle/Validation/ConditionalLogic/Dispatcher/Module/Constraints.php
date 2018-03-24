<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Constraints implements ModuleInterface
{
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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'formData'             => [],
            'field'                => NULL,
            'availableConstraints' => [],
            'appliedConditions'    => []
        ]);

        $resolver->setRequired(['formData', 'field', 'availableConstraints', 'appliedConditions']);
        $resolver->setAllowedTypes('field',FormFieldInterface::class);
        $resolver->setAllowedTypes('formData',['array', 'null']);
        $resolver->setAllowedTypes('availableConstraints','array');
        $resolver->setAllowedTypes('appliedConditions','array');
    }

    /**
     * @param $options
     * @return array
     */
    public function apply($options)
    {
        $this->formData = $options['formData'];
        $this->field = $options['field'];
        $this->availableConstraints = $options['availableConstraints'];
        $this->appliedConditions = $options['appliedConditions'];

        //add defaults
        $constraints = [];
        $fieldConstraints = $this->field->getConstraints();
        foreach ($fieldConstraints as $constraint) {
            $constraints[] = $constraint['type'];
        }

        $validConstraints = $this->checkConditionalLogicConstraints($constraints);

        $constraintData = [];
        foreach($validConstraints as $validConstraint) {
            $key = array_search($validConstraint, array_column($fieldConstraints, 'type'));
            $constraintData[] = $fieldConstraints[$key];
        }

        return $this->appendConstraintsData($constraintData);
    }

    /**
     * Constraints from current conditional logic
     *
     * @param $defaultFieldConstraints
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
                    $defaultFieldConstraints[] = $constraint;
                }
            } elseif ($returnStack->getActionType() === 'removeConstraints') {
                if ($returnStack->getData() === 'all') {
                    $defaultFieldConstraints = [];
                } else {
                    foreach ($returnStack->getData() as $constraint) {
                        $defaultFieldConstraints = array_diff($defaultFieldConstraints, [$constraint]);
                    }
                }
            }
        }

        return array_unique($defaultFieldConstraints);
    }

    /**
     * @param $constraints
     * @return array
     */
    private function appendConstraintsData($constraints)
    {
        $constraintData = [];
        foreach ($constraints as $constraint) {

            $constraintType = $constraint['type'];
            $constraintConfig = $constraint['config'];

            if (!isset($this->availableConstraints[$constraintType])) {
                continue;
            }

            $class = $this->availableConstraints[$constraintType]['class'];
            $constraintData[] = new $class($constraintConfig);
        }

        return $constraintData;
    }

}