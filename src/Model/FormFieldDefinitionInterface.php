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

interface FormFieldDefinitionInterface extends FieldDefinitionInterface
{
    public function setOrder(int $order): void;

    public function setName(string $name): void;

    public function setDisplayName(string $name): void;

    public function getDisplayName(): string;

    public function setType(string $type): void;

    public function setOptions(array $options = []): void;

    public function getOptions(): array;

    public function setOptional(array $options = []): void;

    public function getOptional(): array;

    public function setConstraints(array $constraints = []): void;

    public function getConstraints(): array;
}
