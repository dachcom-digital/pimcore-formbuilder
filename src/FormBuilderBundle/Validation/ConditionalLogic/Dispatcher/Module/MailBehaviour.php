<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\MailBehaviourData;
use FormBuilderBundle\Validation\ConditionalLogic\Factory\DataFactory;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailBehaviour implements ModuleInterface
{
    protected DataFactory $dataFactory;
    protected array $formData = [];
    protected array $appliedConditions = [];
    protected array $availableConstraints = [];
    protected bool $isCopyMail;

    public function __construct(DataFactory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'formData'             => [],
            'appliedConditions'    => [],
            'availableConstraints' => [],
            'isCopy'               => false
        ]);

        $resolver->setRequired(['formData', 'appliedConditions']);
        $resolver->setAllowedTypes('formData', ['array', 'null']);
        $resolver->setAllowedTypes('appliedConditions', 'array');
        $resolver->setAllowedTypes('isCopy', 'boolean');
    }

    public function apply(array $options): DataInterface
    {
        $this->formData = $options['formData'];
        $this->availableConstraints = $options['availableConstraints'];
        $this->appliedConditions = $options['appliedConditions'];
        $this->isCopyMail = $options['isCopy'];

        $returnContainer = $this->dataFactory->generate(MailBehaviourData::class);

        if (empty($this->appliedConditions)) {
            return $returnContainer;
        }

        $mailConfig = [];

        /** @var ReturnStackInterface $returnStack */
        foreach ($this->appliedConditions as $ruleId => $returnStack) {
            if (!$returnStack instanceof SimpleReturnStack || !in_array($returnStack->getActionType(), ['mailBehaviour'])) {
                continue;
            }

            $returnStackData = $returnStack->getData();
            if (empty($returnStackData)) {
                continue;
            }

            if ($this->isCopyMail === true && $returnStackData['mailType'] !== 'copy') {
                continue;
            } elseif ($this->isCopyMail === false && $returnStackData['mailType'] !== 'main') {
                continue;
            }

            $mailConfig[$returnStackData['identifier']] = $returnStackData['value'];
        }

        $returnContainer->setData($mailConfig);

        return $returnContainer;
    }
}
