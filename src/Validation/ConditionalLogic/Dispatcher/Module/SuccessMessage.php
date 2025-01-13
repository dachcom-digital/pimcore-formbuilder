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

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\SuccessMessageData;
use FormBuilderBundle\Validation\ConditionalLogic\Factory\DataFactory;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuccessMessage implements ModuleInterface
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

        $returnContainer = $this->dataFactory->generate(SuccessMessageData::class);

        if (!$returnContainer instanceof DataInterface) {
            throw new \Exception('Could not create SuccessMessage container');
        }

        if (empty($this->appliedConditions)) {
            return $returnContainer;
        }

        $successMessageConfig = [];

        /** @var ReturnStackInterface $returnStack */
        foreach ($this->appliedConditions as $ruleId => $returnStack) {
            if (!$returnStack instanceof SimpleReturnStack || $returnStack->getActionType() !== 'successMessage') {
                continue;
            }

            $returnStackData = $returnStack->getData();
            if (empty($returnStackData)) {
                continue;
            }

            $successMessageConfig[$returnStackData['identifier']] = $returnStackData['value'];
            $successMessageConfig['flashMessage'] = $returnStackData['flashMessage'];
        }

        $returnContainer->setData($successMessageConfig);

        return $returnContainer;
    }
}
