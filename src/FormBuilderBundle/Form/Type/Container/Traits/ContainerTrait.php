<?php

namespace FormBuilderBundle\Form\Type\Container\Traits;

use FormBuilderBundle\Form\Type\ContainerCollectionType;
use Symfony\Component\Form\FormInterface;

trait ContainerTrait
{
    protected function getContainerLabel(array $options): ?string
    {
        if (!isset($options['formbuilder_configuration']['label'])) {
            return null;
        }

        if (empty($options['formbuilder_configuration']['label'])) {
            $label = false;
        } else {
            $label = (string) $options['formbuilder_configuration']['label'];
        }

        return $label;
    }

    protected function addEmptyCollections(FormInterface $form, array $entryOptions, int $counter = 1): void
    {
        for ($i = 0; $i < $counter; $i++) {
            $form->add($i, ContainerCollectionType::class, $entryOptions);
        }
    }
}
