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

namespace FormBuilderBundle\Model;

use FormBuilderBundle\Model\Fragment\SubFieldsAwareInterface;

interface FormFieldContainerDefinitionInterface extends FieldDefinitionInterface, SubFieldsAwareInterface
{
    public function setOrder(int $order): void;

    public function setName(string $name): void;

    public function setDisplayName(string $name): void;

    public function getDisplayName(): string;

    public function setType(string $type): void;

    public function setSubType(string $subType): void;

    public function getSubType(): string;

    public function setConfiguration(array $configuration = []): void;

    public function getConfiguration(): array;
}
