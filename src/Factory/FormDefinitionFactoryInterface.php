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

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinition;

interface FormDefinitionFactoryInterface
{
    public function createFormDefinition(): FormDefinitionInterface;

    public function createFormFieldDefinition(): FormFieldDefinitionInterface;

    public function createFormFieldContainerDefinition(): FormFieldContainerDefinitionInterface;

    public function createFormFieldDynamicDefinition(string $name, string $type, array $options, array $optional = []): FormFieldDynamicDefinition;
}
