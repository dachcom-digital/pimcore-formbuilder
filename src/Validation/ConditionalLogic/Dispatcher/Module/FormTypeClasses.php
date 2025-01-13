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

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\FormTypeClassesData;
use FormBuilderBundle\Validation\ConditionalLogic\Factory\DataFactory;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeClasses implements ModuleInterface
{
    protected array $formData;
    protected FieldDefinitionInterface $field;
    protected array $appliedConditions;

    public function __construct(protected DataFactory $dataFactory)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
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
     * {@inheritDoc}
     */
    public function apply(array $options): DataInterface
    {
        $this->formData = $options['formData'];
        $this->field = $options['field'];
        $this->appliedConditions = $options['appliedConditions'];

        return $this->checkConditionData();
    }

    /**
     * @throws \Exception
     */
    private function checkConditionData(): DataInterface
    {
        $returnContainer = $this->dataFactory->generate(FormTypeClassesData::class);

        if (!$returnContainer instanceof DataInterface) {
            throw new \Exception('Could not create FormTypeClasses container');
        }

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
