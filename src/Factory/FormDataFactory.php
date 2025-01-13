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

use FormBuilderBundle\Form\Data\FormData;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;

class FormDataFactory implements FormDataFactoryInterface
{
    public function createFormData(FormDefinitionInterface $formDefinition, array $data = []): FormDataInterface
    {
        return new FormData($formDefinition, $data);
    }
}
