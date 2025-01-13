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

namespace FormBuilderBundle\OutputWorkflow\Channel\Object\Helper;

use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use Pimcore\Model\DataObject;
use Symfony\Contracts\Translation\TranslatorInterface;

class FieldCollectionValidationHelper
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected array $validationConfiguration
    ) {
    }

    /**
     * @throws GuardOutputWorkflowException
     */
    public function validate(
        DataObject\Concrete $referenceObject,
        DataObject\Fieldcollection $fieldCollection,
        DataObject\Fieldcollection\Data\AbstractData $data
    ): void {
        foreach ($this->validationConfiguration as $validationBlock) {
            if ($validationBlock['enabled'] === false) {
                continue;
            }

            if ($validationBlock['type'] === 'unique') {
                $this->validateUniqueness($validationBlock['field'], $validationBlock['message'], $fieldCollection, $data);
            }

            if ($validationBlock['type'] === 'count') {
                $this->validateCount($validationBlock['field'], $validationBlock['message'], $referenceObject, $fieldCollection);
            }
        }
    }

    /**
     * @throws GuardOutputWorkflowException
     */
    protected function validateUniqueness(
        string $uniqueFieldName,
        string $validationMessage,
        DataObject\Fieldcollection $fieldCollection,
        DataObject\Fieldcollection\Data\AbstractData $currentFieldCollection
    ): void {
        if ($fieldCollection->getCount() === 0) {
            return;
        }

        $uniqueFieldGetter = sprintf('get%s', ucfirst($uniqueFieldName));

        /** @var DataObject\Fieldcollection\Data\AbstractData $fieldCollection */
        foreach ($fieldCollection->getItems() as $fieldCollection) {
            if (!method_exists($fieldCollection, $uniqueFieldGetter)) {
                break;
            }

            if (!method_exists($currentFieldCollection, $uniqueFieldGetter)) {
                break;
            }

            if (($currentFieldCollection->getType() !== $fieldCollection->getType())) {
                break;
            }

            if ($fieldCollection->$uniqueFieldGetter() === $currentFieldCollection->$uniqueFieldGetter()) {
                $message = str_replace(['%field_value%'], [$currentFieldCollection->$uniqueFieldGetter()], $this->translator->trans($validationMessage));

                throw new GuardOutputWorkflowException($message);
            }
        }
    }

    /**
     * @throws GuardOutputWorkflowException
     */
    protected function validateCount(
        string $countFieldName,
        string $validationMessage,
        DataObject\Concrete $referenceObject,
        DataObject\Fieldcollection $fieldCollection
    ): void {
        $countFieldGetter = sprintf('get%s', ucfirst($countFieldName));

        if (!method_exists($referenceObject, $countFieldGetter)) {
            return;
        }

        $countData = $referenceObject->$countFieldGetter();

        if (!is_numeric($countData)) {
            return;
        }

        // validation happens before the new field collection gets appended.
        if ($fieldCollection->getCount() === (int) $countData) {
            $message = str_replace(['%count%'], [$countData], $this->translator->trans($validationMessage));

            throw new GuardOutputWorkflowException($message);
        }
    }
}
