<?php

namespace FormBuilderBundle\OutputWorkflow\DynamicObjectResolver;

use Pimcore\Model\DataObject;
use Symfony\Component\Form\FormInterface;

class ObjectByRuntimeDataResolver implements DynamicObjectResolverInterface
{
    protected string $runtimeDataId;
    protected string $objectIdentifier;
    protected bool $isLocalizedValue;

    public function __construct(string $runtimeDataId, string $objectIdentifier, bool $isLocalizedValue = false)
    {
        $this->runtimeDataId = $runtimeDataId;
        $this->objectIdentifier = $objectIdentifier;
        $this->isLocalizedValue = $isLocalizedValue;
    }

    public function resolve(FormInterface $form, DataObject $referenceObject, array $formRuntimeData, string $locale): ?DataObject\Concrete
    {
        if (!isset($formRuntimeData[$this->runtimeDataId])) {
            return null;
        }

        if (!$referenceObject instanceof DataObject\Concrete) {
            return null;
        }

        $dataObjectIdentifier = $formRuntimeData[$this->runtimeDataId];
        $pathName = sprintf('\Pimcore\Model\DataObject\%s', ucfirst($referenceObject->getClassName()));

        if ($this->objectIdentifier === 'id' && method_exists($pathName, 'getById')) {
            return $pathName::getById($dataObjectIdentifier);
        }

        if ($this->isLocalizedValue === true) {
            if (is_callable([$pathName, 'getByLocalizedfields'])) {
                return $pathName::getByLocalizedfields($this->objectIdentifier, $dataObjectIdentifier, $locale, ['limit' => 1]);
            }

            return null;
        }

        $getter = sprintf('getBy%s', ucfirst($this->objectIdentifier));
        if (is_callable([$pathName, $getter])) {
            $listing = $pathName::$getter($dataObjectIdentifier);
            if ($listing instanceof DataObject\Listing) {
                $objects = $listing->getObjects();
                if (count($objects) === 1) {
                    return $objects[0];
                }
            }
        }

        return null;
    }
}
