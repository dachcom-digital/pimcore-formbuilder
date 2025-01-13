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

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormFieldContainerDefinition;
use FormBuilderBundle\Model\FormFieldDefinition;
use FormBuilderBundle\Model\FormFieldDynamicDefinition;

class FormDefinitionFactory implements FormDefinitionFactoryInterface
{
    public function createFormDefinition(): FormDefinition
    {
        return new FormDefinition();
    }

    public function createFormFieldDefinition(): FormFieldDefinition
    {
        return new FormFieldDefinition();
    }

    public function createFormFieldContainerDefinition(): FormFieldContainerDefinition
    {
        return new FormFieldContainerDefinition();
    }

    public function createFormFieldDynamicDefinition(string $name, string $type, array $options, array $optional = []): FormFieldDynamicDefinition
    {
        return new FormFieldDynamicDefinition($name, $type, $options, $optional);
    }
}
