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

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDefinitionRepositoryInterface
{
    public function findById(int $id): ?FormDefinitionInterface;

    public function findByName(string $name): ?FormDefinitionInterface;

    public function findNameById(int $id): ?string;

    public function findIdByName(string $name): ?int;

    public function findAll(): array;
}
