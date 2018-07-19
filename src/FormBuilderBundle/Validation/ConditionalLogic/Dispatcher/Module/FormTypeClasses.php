<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\FormTypeClassesData;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeClasses implements ModuleInterface
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
            'formData'          => [],
            'field'             => null,
            'appliedConditions' => []
        ]);

        $resolver->setRequired(['formData', 'field', 'appliedConditions']);
        $resolver->setAllowedTypes('field', FormFieldInterface::class);
        $resolver->setAllowedTypes('formData', ['array', 'null']);
        $resolver->setAllowedTypes('appliedConditions', 'array');
    }

    /**
     * @param $options
     * @return array
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
        $returnContainer = new FormTypeClassesData();

        if (empty($this->appliedConditions)) {
            return $returnContainer;
        }

        $classes = [];

        /** @var ReturnStackInterface $returnStack */
        foreach ($this->appliedConditions as $ruleId => $returnStack) {
            if (!$returnStack instanceof FieldReturnStack || !in_array($returnStack->getActionType(), [
                    'toggleClass',
                    'toggleElement'
                ])) {
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