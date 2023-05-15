<?php

namespace FormBuilderBundle\OutputWorkflow\DynamicObjectResolver;

use Pimcore\Model\DataObject;
use Symfony\Component\Form\FormInterface;

interface DynamicObjectResolverInterface
{
    public function resolve(FormInterface $form, string $dataClass, array $formRuntimeData, string $locale, string $objectResolverMode): ?DataObject;

    public static function getAllowedObjectResolverModes(): array;
}
