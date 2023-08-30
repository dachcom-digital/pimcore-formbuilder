<?php

namespace FormBuilderBundle\OutputWorkflow\DynamicObjectResolver;

use FormBuilderBundle\OutputWorkflow\Channel\Object\AbstractObjectResolver;
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

    public static function getAllowedObjectResolverModes(): array
    {
        return [
            AbstractObjectResolver::OBJECT_RESOLVER_UPDATE
        ];
    }

    public function resolve(FormInterface $form, string $dataClass, array $formRuntimeData, string $locale, string $objectResolverMode): ?DataObject
    {
        if (!isset($formRuntimeData[$this->runtimeDataId])) {
            return null;
        }

        if ($objectResolverMode !== AbstractObjectResolver::OBJECT_RESOLVER_UPDATE) {
            return null;
        }

        $dataObjectIdentifier = $formRuntimeData[$this->runtimeDataId];
        $pathName = sprintf('\Pimcore\Model\DataObject\%s', $dataClass);

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
