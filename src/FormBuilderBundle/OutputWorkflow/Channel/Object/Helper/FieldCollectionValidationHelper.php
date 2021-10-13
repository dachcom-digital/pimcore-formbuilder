<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object\Helper;

use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use Pimcore\Translation\Translator;
use Pimcore\Model\DataObject;

class FieldCollectionValidationHelper
{
    protected Translator $translator;
    protected array $validationConfiguration;

    public function __construct(array $validationConfiguration)
    {
        $this->translator = \Pimcore::getContainer()->get('translator');
        $this->validationConfiguration = $validationConfiguration;
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
