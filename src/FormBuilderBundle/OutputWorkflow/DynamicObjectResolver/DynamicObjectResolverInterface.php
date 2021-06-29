<?php

namespace FormBuilderBundle\OutputWorkflow\DynamicObjectResolver;

use Pimcore\Model\DataObject;
use Symfony\Component\Form\FormInterface;

interface DynamicObjectResolverInterface
{
    public function resolve(FormInterface $form, DataObject $referenceObject, array $formRuntimeData, string $locale): ?DataObject\Concrete;
}
