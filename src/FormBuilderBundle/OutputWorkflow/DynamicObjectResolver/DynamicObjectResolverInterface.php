<?php

namespace FormBuilderBundle\OutputWorkflow\DynamicObjectResolver;

use Pimcore\Model\DataObject;
use Symfony\Component\Form\FormInterface;

interface DynamicObjectResolverInterface
{
    /**
     * @param FormInterface $form
     * @param DataObject    $referenceObject
     * @param array         $formRuntimeData
     * @param string        $locale
     *
     * @return DataObject|null
     */
    public function resolve(FormInterface $form, DataObject $referenceObject, array $formRuntimeData, string $locale);
}
