<?php

namespace FormBuilderBundle\Form\Type\Container\Traits;

use FormBuilderBundle\Form\Type\ContainerCollectionType;
use Symfony\Component\Form\FormInterface;

trait ContainerTrait
{
    /**
     * @param array $options
     *
     * @return bool|string
     */
    protected function getContainerLabel(array $options)
    {
        if (!isset($options['formbuilder_configuration']['label'])) {
            return false;
        }

        if (empty($options['formbuilder_configuration']['label'])) {
            $label = false;
        } else {
            $label = (string)$options['formbuilder_configuration']['label'];
        }

        return $label;
    }

    /**
     * @param FormInterface $form
     * @param array         $entryOptions
     * @param int           $counter
     */
    protected function addEmptyCollections(FormInterface $form, array $entryOptions, int $counter = 1)
    {
        for ($i = 0; $i < $counter; $i++) {
            $form->add($i, ContainerCollectionType::class, $entryOptions);
        }
    }
}