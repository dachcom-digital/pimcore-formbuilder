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

namespace FormBuilderBundle\Form\Type\Container\Traits;

use FormBuilderBundle\Form\Type\ContainerCollectionType;
use Symfony\Component\Form\FormInterface;

trait ContainerTrait
{
    protected function getContainerLabel(array $options): bool|string
    {
        if (!isset($options['formbuilder_configuration']['label'])) {
            return false;
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
