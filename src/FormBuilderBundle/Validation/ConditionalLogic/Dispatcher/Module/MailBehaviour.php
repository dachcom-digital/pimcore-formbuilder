<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Storage\FormInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailBehaviour implements ModuleInterface
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
     * @var array
     */
    protected $appliedConditions;

    /**
     * @var array
     */
    protected $availableConstraints;

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'formData'          => [],
            'appliedConditions' => [],
            'availableConstraints' => []
        ]);

        $resolver->setRequired(['formData', 'appliedConditions']);
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
        $this->availableConstraints = $options['availableConstraints'];
        $this->appliedConditions = $options['appliedConditions'];

        if (empty($this->appliedConditions)) {
            return [];
        }

        $mailConfig = [];
        /** @var ReturnStackInterface $returnStack */
        foreach ($this->appliedConditions as $ruleId => $returnStack) {

            if (!$returnStack instanceof SimpleReturnStack || !in_array($returnStack->getActionType(), ['mailBehaviour'])) {
                continue;
            }

            foreach ($returnStack->getData() as $identifier => $value) {
                $mailConfig[$identifier] = $value;
            }
        }

        return $mailConfig;
    }

}